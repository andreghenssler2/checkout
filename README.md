# Checkout IECLB Parobé — v1.4.6

Aplicação independente para:

- Campanhas de ofertas;
- Formulários de palpites de jogos;
- Pagamentos via PIX;
- Pagamentos via Cartão de Crédito;
- Integração com Asaas API v3;
- Administração própria;
- Webhook próprio do Asaas.

Domínio oficial:

`https://checkout.ieclbparobe.com.br/`

## Palpites

O administrador pode criar um formulário para um jogo contendo:

- título;
- equipe 1;
- equipe 2;
- data/hora do jogo;
- início e encerramento das participações;
- descrição/regras;
- opções de placar, uma por linha;
- opção "Outro";
- valores fixos;
- valor livre opcional;
- valor mínimo nunca inferior a R$ 10,00;
- PIX;
- Cartão de Crédito;
- imagem;
- ativação do formulário.

O participante informa:

- palpite;
- valor;
- nome completo;
- CPF;
- e-mail;
- telefone/WhatsApp;
- forma de pagamento.

Os dados completos do cartão não são armazenados no banco de dados.

## Atualização de v1.0.2 para v1.4.6

1. Faça backup dos arquivos e do banco.
2. Importe:
   `database/migrations/20260808_palpites.sql`
3. Substitua os arquivos pelos da versão 1.1.0.
4. Garanta permissão de escrita em:
   `uploads/palpites/`
5. Acesse:
   `https://checkout.ieclbparobe.com.br/admin/palpites/`

Não execute novamente o instalador em uma aplicação que já esteja instalada.

## Instalação nova

Acesse:

`https://checkout.ieclbparobe.com.br/install/`

O instalador utiliza `database/schema.sql`, que já contém as tabelas de Ofertas e Palpites.

## Webhook Asaas

`https://checkout.ieclbparobe.com.br/api/asaas/webhook.php`

Ofertas e palpites utilizam a mesma configuração Asaas da aplicação Checkout.

## HTTPS

HTTPS é obrigatório no domínio de produção para evitar Mixed Content e proteger os dados enviados no checkout.


## v1.4.6

- Tela personalizada para Oferta ou Palpite inexistente, futuro, encerrado ou desativado.
- Estado personalizado na página inicial quando não há opções disponíveis.
- Ao selecionar PIX, o checkout mostra:
  "O QR Code e código do Pix serão exibidos após clicar no botão Pagar agora".
- Nenhuma alteração de banco é necessária para atualizar da v1.1.0 para v1.4.6.


## Slugs automáticos — v1.4.6

Os slugs não são mais digitados pelo administrador.

### Oferta

É criado diretamente do nome da Oferta.

Exemplo:

`Missão no Sínodo Mato Grosso`

gera:

`missao-no-sinodo-mato-grosso`

### Palpite

É criado pela data do jogo, Equipe 1 e Equipe 2.

Exemplo:

Data: `09/08/2026`
Equipe 1: `Brasil`
Equipe 2: `Escócia`

gera:

`09-08-2026-brasil-escocia`

Se já existir um slug igual, o sistema acrescenta automaticamente `-2`,
`-3` e assim por diante.

Não é necessária alteração no banco de dados.


## Slug do Palpite — v1.4.6

O slug do Palpite passa a ser gerado diretamente pelo título.

Exemplo:

Título:

`Copa do Mundo da JEP - Brasil x Escócia`

gera:

`copa-do-mundo-da-jep-brasil-x-escocia`

A data e as equipes continuam sendo obrigatórias no cadastro do jogo,
mas não fazem mais parte do slug.

Não é necessária alteração no banco de dados.


## Boleto nas Ofertas — v1.4.6

O Checkout passa a aceitar Boleto somente nas Ofertas.

### Regras

- Palpites continuam com PIX e Cartão de Crédito; Boleto não é permitido.
- O vencimento do Boleto é 1 dia útil após a geração.
- Se o vencimento calculado cair em sábado ou domingo, passa para o próximo
  dia útil.
- Se a data de encerramento da Oferta for no mesmo dia do vencimento ou antes,
  a opção Boleto não é disponibilizada.
- Isso impede, por exemplo, gerar Boleto quando a Oferta fecha no dia seguinte
  e o Boleto venceria nesse mesmo dia.
- O backend repete a validação, portanto não é possível forçar a geração
  alterando o HTML do checkout.

### Asaas

A cobrança é criada como `billingType=BOLETO`. Depois da criação, o sistema
recupera a linha digitável pelo endpoint da cobrança e salva também a URL do
boleto retornada pelo Asaas.

### Atualização

Para atualizar uma instalação v1.1.x:

1. Faça backup do banco e dos arquivos.
2. Importe `database/migrations/20260808_boleto_ofertas.sql`.
3. Substitua os arquivos pelos da v1.4.6.
4. Entre em Admin > Ofertas e habilite Boleto nas campanhas desejadas.


## E-mails e comprovante — v1.4.6

O Checkout passa a enviar dois e-mails automáticos:

1. ao criar uma Oferta/Palpite e gerar o pagamento;
2. quando o pagamento é aprovado.

Quando o pagamento é aprovado, o sistema cria um comprovante único contendo:

- número do comprovante;
- nome do pagador;
- CPF parcialmente mascarado;
- Oferta ou Palpite;
- palpite escolhido, quando aplicável;
- valor pago;
- forma de pagamento;
- data e hora;
- código da transação;
- status Pago.

O comprovante possui uma URL segura com token aleatório e pode ser impresso ou
salvo como PDF pelo navegador.

### Configuração do e-mail

Acesse:

`Admin > E-mail`

O envio usa a função `mail()` do PHP da hospedagem. Configure um remetente do
domínio e faça um envio de teste.

### Fila e reenvio

Todos os e-mails ficam registrados em `emails_envios`.

O sistema tenta enviar imediatamente. Se houver falha, o cron pode repetir os
envios pendentes.

Exemplo de comando:

`php /CAMINHO/checkout/cron/processar-emails.php`

No cPanel, recomenda-se executar a cada 5 minutos.

### Atualização da v1.2.0

1. Faça backup dos arquivos e banco.
2. Importe `database/migrations/20260808_email_comprovante.sql`.
3. Substitua os arquivos pelos da v1.4.6.
4. Entre em `Admin > E-mail` e confira remetente e Reply-To.
5. Envie um e-mail de teste.
6. Configure o cron de reenvio.


## Transparência sobre os pagamentos — v1.4.6

As páginas públicas de Oferta e Palpite exibem, antes do botão de pagamento,
um aviso de transparência informando que:

- os pagamentos são processados em nome de André Gustavo Henssler;
- André atua somente como intermediador do recebimento;
- o recebimento ocorre em parceria com a IECLB Parobé;
- 100% do valor arrecadado é repassado à IECLB Parobé.

Uma versão resumida também aparece na página de pagamento e no comprovante.

Não é necessária alteração no banco de dados.


## Notificações do Asaas — v1.4.6

As notificações padrão de cobrança do Asaas ficam desabilitadas para o
pagador. O Checkout é responsável pelos e-mails de criação da cobrança e
aprovação do pagamento.

O sistema envia `notificationDisabled = true`:

- ao criar um novo cliente no Asaas;
- ao reutilizar um cliente já existente, antes de criar nova cobrança.

Em `Admin > Asaas > Gerenciar notificações Asaas` existe uma ação para
aplicar a configuração imediatamente a todos os clientes já vinculados ao
Checkout.

Não é necessária alteração no banco de dados.


## Diagnóstico do PIX — v1.4.6

Em `Admin > Asaas` existe agora a área `Diagnóstico do PIX`.

Ela utiliza a mesma API Key e o mesmo ambiente selecionados no Checkout para:

- consultar `GET /v3/pix/addressKeys?status=ACTIVE`;
- informar se existe uma chave Pix ativa;
- listar as chaves `ACTIVE` encontradas;
- no Sandbox, criar uma chave aleatória `EVP` usando
  `POST /v3/pix/addressKeys`.

No Sandbox há um botão `Criar chave aleatória no Sandbox`.

Por limitação do Banco Central informada pelo Asaas, deve-se aguardar
pelo menos 1 minuto entre criações de novas chaves.

### Bloqueio automático

O resultado do teste fica salvo separadamente para Sandbox e Produção.

A Oferta e o Palpite consultam esse estado e, quando o ambiente está
confirmadamente sem chave Pix ativa:

- a opção PIX deixa de ser selecionável;
- aparece `PIX indisponível`;
- o backend também bloqueia tentativas manuais de forçar PIX.

A verificação é atualizada automaticamente, com cache de aproximadamente
10 minutos, para evitar uma chamada ao Asaas em cada carregamento.

### Atualização da v1.3.x

1. Faça backup do banco e arquivos.
2. Importe `database/migrations/20260808_pix_diagnostico.sql`.
3. Substitua os arquivos pelos da v1.4.6.
4. Entre em `Admin > Asaas`.
5. Selecione Sandbox e clique em `Testar PIX`.
6. Se não houver chave, use `Criar chave aleatória no Sandbox`.


## Diagnóstico de criação de cobranças — v1.4.6

Esta versão melhora o diagnóstico quando o Asaas cria o cliente, mas recusa
a cobrança.

### Situação cadastral

`Admin > Asaas` consulta:

`GET /v3/myAccount/status/`

e mostra:

- aprovação geral;
- dados comerciais;
- documentação;
- conta bancária.

No Sandbox, se a conta não estiver aprovada, há um botão:

`Aprovar conta Sandbox`

que chama:

`POST /v3/sandbox/myAccount/approve`

### Validação antes da cobrança

Antes de criar/reutilizar o cliente e emitir a cobrança, o Checkout verifica
se `general = APPROVED`.

Isso evita criar clientes quando o ambiente ainda não está habilitado para
receber cobranças.

### Erros mais completos

Os erros da API agora registram:

- status HTTP;
- endpoint;
- código retornado pelo Asaas;
- descrição do erro.

Exemplo:

`Asaas HTTP 400 em POST /payments: [invalid_billingType] ...`

Esses erros aparecem também em:

`Admin > Pagamentos > Erro / retorno`

Não é necessária alteração no banco de dados.


## PIX Sandbox com fallback — v1.4.6

Foi confirmado um cenário no Sandbox em que:

- situação cadastral = APPROVED;
- existem chaves Pix ACTIVE;
- mesmo assim `POST /v3/payments` com `billingType=PIX`
  retorna `invalid_billingType`.

O Checkout agora trata esse caso automaticamente.

### Sandbox

1. tenta criar a cobrança como `PIX`;
2. se funcionar, segue normalmente;
3. se retornar especificamente `invalid_billingType`, cria a cobrança
   novamente como `UNDEFINED`;
4. recupera o QR Code em `/payments/{id}/pixQrCode`;
5. para o usuário do Checkout, a forma continua sendo PIX.

### Produção

Não existe fallback em Produção. A cobrança continua sendo criada
diretamente com `billingType=PIX`.

Não é necessária alteração no banco de dados.


## Correção PIX indisponível no Sandbox — v1.4.6

O diagnóstico administrativo e a disponibilidade do PIX no formulário agora
são tratados separadamente.

No Sandbox, se a integração Asaas estiver ativa e existir API Key configurada,
o PIX permanece disponível no formulário. O resultado armazenado do teste de
chaves não bloqueia mais Oferta ou Palpite.

O processamento continua seguro:

1. tenta `billingType=PIX`;
2. em Sandbox, somente se houver `invalid_billingType`, tenta `UNDEFINED`;
3. recupera o QR Code em `/payments/{id}/pixQrCode`.

Em Produção, o diagnóstico normal de chave ativa continua sendo respeitado e
não existe fallback para `UNDEFINED`.

Não é necessária alteração no banco de dados.


## Cobrança criada antes do QR Code — v1.4.6

Foi corrigido o fluxo observado no Sandbox:

1. `POST /payments` com PIX pode falhar;
2. o fallback `UNDEFINED` pode criar a cobrança com sucesso;
3. o Asaas envia imediatamente `PAYMENT_CREATED`;
4. `GET /payments/{id}/pixQrCode` pode falhar com
   `pix.receivingWithPixDisabled`.

Antes, a aplicação só salvava `asaasPaymentId` depois de obter o QR Code.
Assim, uma falha no QR Code fazia uma cobrança já criada no Asaas aparecer
como recusada na aplicação.

Agora:

- a cobrança é salva imediatamente após o sucesso de `POST /payments`;
- a obtenção do QR Code é uma segunda etapa;
- falha no QR Code mantém o pagamento como `Pendente`;
- a tela informa que a cobrança já existe e não deve ser criada novamente;
- existe o botão `Tentar carregar QR Code novamente`, que NÃO cria outra
  cobrança;
- o webhook `PAYMENT_CREATED` pode reconciliar usando
  `payment.externalReference = pagamentos.codigo`, mesmo se chegar antes
  de a resposta do POST ser persistida;
- `Admin > Pagamentos` possui o botão `Reconciliar` para cobranças antigas
  que foram criadas no Asaas, mas ficaram sem `asaasPaymentId` local.

A reconciliação consulta:

`GET /v3/payments?externalReference={codigo}`

e não cria nenhuma nova cobrança.

Não é necessária alteração no banco de dados.


## Webhook Sandbox/Produção — v1.4.6

Foi corrigida a autenticação do endpoint de webhook.

Antes, `api/asaas/webhook.php` validava somente o token do ambiente
selecionado no Admin. Como Sandbox e Produção utilizam a mesma URL pública,
trocar o ambiente podia fazer o outro ambiente receber HTTP 401.

Agora o endpoint aceita os dois tokens configurados:

- `webhook_token_sandbox`;
- `webhook_token_producao`.

Cada requisição continua precisando apresentar um token válido no header
`asaas-access-token`.

### Eventos

O endpoint do Checkout é destinado a cobranças.

Eventos que não começam com `PAYMENT_`, como `ACCESS_TOKEN_CREATED`, são
autenticados e respondidos com HTTP 200, mas não são processados nem alteram
pagamentos.

### Sincronização pelo Admin

Em `Admin > Asaas` existe o botão:

`Sincronizar webhook de pagamentos`

Ele usa a API do ambiente selecionado para criar ou atualizar o webhook,
configurando:

- URL oficial do Checkout;
- token do ambiente atual;
- `enabled = true`;
- `interrupted = false`;
- envio sequencial;
- somente eventos relevantes de pagamento.

Isso também permite reativar uma fila que tenha sofrido penalização por
retornos 401 anteriores.

Não é necessária alteração no banco de dados.


## Pix desabilitado no Sandbox — v1.4.6

Quando o Asaas retorna `pix.receivingWithPixDisabled`, a aplicação reconhece
que a cobrança já foi criada e que a indisponibilidade é específica do
recebimento Pix da conta Sandbox.

A tela pública não exibe mais o erro técnico e não oferece repetição
indefinida da busca do QR Code nesse caso.

Em vez disso, informa que:

- a cobrança de teste já existe;
- o QR Code não está disponível nessa conta Sandbox;
- a cobrança não deve ser criada novamente;
- a confirmação pode ser feita manualmente no painel do Asaas Sandbox;
- o webhook continuará atualizando o status do pagamento.

Produção não foi alterada.

Não é necessária alteração no banco de dados.


## Limpeza do fluxo PIX — v1.4.7

Após a correção da configuração da conta Asaas, foi removido o fallback
temporário utilizado no Sandbox.

A partir da v1.4.7:

- PIX é criado sempre com `billingType=PIX`;
- não existe mais tentativa automática com `billingType=UNDEFINED`;
- Sandbox e Produção usam a mesma regra de criação de cobrança PIX;
- a opção PIX só fica disponível quando a integração está ativa,
  existe API Key configurada e o diagnóstico encontra chave Pix `ACTIVE`;
- permanece a validação da situação cadastral da conta;
- permanece o diagnóstico de chaves Pix;
- permanece o tratamento separado entre criação da cobrança e obtenção
  do QR Code;
- permanece a reconciliação por webhook e `externalReference`;
- permanece a sincronização de webhook de Sandbox e Produção;
- permanece o tratamento amigável para erros de QR Code e para
  `pix.receivingWithPixDisabled`, caso a opção de recebimento seja
  desabilitada novamente no Asaas.

### Configuração necessária no Asaas

Além de possuir chave Pix `ACTIVE`, a conta do ambiente atual precisa estar
com o recebimento via Pix habilitado.

Não é necessária alteração no banco de dados.


## Transparência sobre tarifas — v1.4.8

O texto de transparência foi atualizado para diferenciar:

- valor bruto pago pela pessoa;
- eventuais tarifas cobradas pelo meio de pagamento;
- valor líquido efetivamente recebido;
- repasse integral do valor líquido à IECLB Parobé.

O Checkout não informa uma tarifa fixa, pois as tarifas podem variar conforme
o meio de pagamento, contrato e condições aplicadas pelo prestador financeiro.

O aviso completo aparece nos formulários de Oferta e Palpite. A página de
pagamento e o comprovante usam versões resumidas coerentes com o mesmo texto.

Não é necessária alteração no banco de dados.


## Validade do Pix Copia e Cola — v1.4.9

Foi adicionado o aviso de que a chave Pix Copia e Cola tem validade de
1 hora após a geração.

O aviso aparece ao selecionar PIX em Oferta e Palpite e também na página
de pagamento junto ao código Pix Copia e Cola.

Não é necessária alteração no banco de dados.


## Relatórios e acompanhamento de jogos — v1.5.0

### Relatório de Ofertas

Novo menu `Admin > Relatórios > Relatório de Ofertas`.

Permite filtrar por:

- Oferta;
- forma de pagamento;
- data inicial/final;
- agrupamento por dia, mês ou ano.

Mostra:

- quantidade de pagamentos recebidos;
- valor bruto;
- valor líquido informado pelo Asaas;
- tarifas registradas;
- valores separados por PIX, Cartão e Boleto;
- pagamentos individuais e pagador.

### Valor líquido e tarifa

A v1.5.0 passa a armazenar `netValue` retornado pelo Asaas em
`pagamentos.valorLiquido`.

Quando o Asaas informa `value` e `netValue`, o Checkout calcula a tarifa como:

`taxa = valor bruto - valor líquido`

Pagamentos anteriores à v1.5.0 podem não possuir esses valores históricos.

### Relatório de Palpites

Novo menu `Admin > Relatórios > Relatório de Palpites`.

Mostra:

- total de palpites;
- palpites pagos;
- valor recebido;
- valor líquido/tarifas;
- quem pagou;
- telefone/e-mail/CPF;
- qual palpite foi marcado;
- resumo por opção de palpite;
- ganhadores de jogos finalizados.

### Acompanhamento do jogo

Em `Admin > Palpites` existe o botão `Acompanhar`.

O administrador pode:

- marcar o jogo como Agendado, Em andamento ou Finalizado;
- atualizar o placar durante a partida;
- filtrar quem está acertando;
- filtrar quem não está acertando;
- filtrar pagos e não pagos;
- finalizar o jogo.

Quando o jogo é finalizado:

- participantes que acertaram exatamente o placar final e possuem
  pagamento `Pago` são considerados ganhadores;
- os ganhadores aparecem primeiro;
- ficam destacados no acompanhamento, participações e relatório.

### Banco de dados

Execute:

`database/migrations/20260808_relatorios_acompanhamento_v1.5.0.sql`

antes de utilizar a v1.5.0 em banco existente.


## Exportação de relatórios em PDF com Dompdf — v1.5.1

Os relatórios de Ofertas e Palpites agora possuem o botão `Exportar PDF`.

A exportação respeita os filtros aplicados na tela.

### Relatório de Ofertas em PDF

Inclui:

- filtros utilizados;
- pagamentos recebidos;
- valor bruto;
- valor líquido;
- tarifas;
- recebimentos agrupados por dia, mês ou ano;
- valores por PIX, Cartão e Boleto;
- resumo por forma de pagamento;
- lista detalhada dos pagamentos e pagadores.

### Relatório de Palpites em PDF

Inclui:

- filtros utilizados;
- total de palpites e palpites pagos;
- valor bruto e líquido;
- ganhadores dos jogos finalizados;
- resumo por palpite marcado;
- participante, contato, CPF, palpite, pagamento, forma e valor;
- ganhadores destacados e listados primeiro.

### Instalação do Dompdf

Na raiz do Checkout execute:

`composer install --no-dev --optimize-autoloader`

O `composer.json` da v1.5.1 inclui:

`dompdf/dompdf: ^3.1.5`

Se o servidor não disponibilizar Composer, também é possível utilizar a
distribuição oficial empacotada do Dompdf, extraindo-a em:

`/lib/dompdf`

de forma que exista:

`/lib/dompdf/autoload.inc.php`

A pasta `vendor`/biblioteca do Dompdf não é incluída no ZIP desta atualização;
ela deve ser instalada no servidor.

### Segurança

- somente administradores autenticados podem gerar os PDFs;
- recursos remotos ficam desativados no Dompdf;
- os PDFs são gerados como download;
- os filtros são revalidados pelo backend.

Não é necessária alteração no banco de dados.


## Correções dos relatórios / Dompdf — v1.5.2

### Warning `Undefined array key "agrupar"`

Corrigido em:

- `admin/relatorios/ofertas.php`
- `admin/relatorios/ofertas-pdf.php`

O filtro agora é normalizado em uma variável antes de montar o array de
filtros, portanto abrir o relatório sem o parâmetro `agrupar` não gera mais
warning.

### Dompdf não instalado

A ausência do Dompdf não gera mais `Fatal error`.

Enquanto a biblioteca não estiver instalada:

- o botão `Exportar PDF` é substituído por `Configurar Dompdf`;
- `Admin > Relatórios` avisa que a dependência está pendente;
- existe `Admin > Relatórios > Diagnóstico do Dompdf`;
- acessar diretamente uma rota `*-pdf.php` retorna uma página amigável com
  as instruções de instalação, em vez de uma exceção não tratada.

### Instalação recomendada

Na raiz do Checkout:

`composer install --no-dev --optimize-autoloader`

A aplicação procura o autoload nestes locais:

- `/checkout/vendor/autoload.php`
- `../vendor/autoload.php`
- `/checkout/lib/dompdf/autoload.inc.php`
- `/checkout/dompdf/autoload.inc.php`

Não há alteração no banco de dados.


## Escolha dos dados no PDF de Palpites — v1.5.3

Ao clicar em `Exportar PDF` no relatório de Palpites, o administrador não
gera mais o arquivo imediatamente.

Primeiro é aberta uma tela para escolher o conteúdo da exportação.

É possível selecionar as seções:

- Resumo geral;
- Ganhadores;
- Resumo por palpite marcado;
- Participantes.

Também é possível escolher quais participantes aparecem:

- Todos;
- Somente pagos;
- Somente ganhadores.

Na tabela de participantes podem ser selecionados individualmente:

- Resultado;
- Jogo;
- Nome;
- E-mail;
- Telefone;
- CPF;
- Palpite;
- Status do pagamento;
- Forma de pagamento;
- Valor pago;
- Valor líquido;
- Data;
- Código Asaas.

Há ainda a opção de mostrar ou ocultar os filtros utilizados no topo do PDF.

Não é necessária alteração no banco de dados.


## Acompanhamento automático dos palpites — v1.5.4

Na tela `Admin > Palpites > Acompanhar`, o placar agora é salvo
automaticamente via AJAX.

Durante o jogo:

- ao alterar o placar, a tabela é recalculada sem clicar em Atualizar;
- quem está acertando o placar atual aparece primeiro;
- filtros de acertando, não acertando, pagos e não pagos funcionam sem
  recarregar a página;
- a tela atualiza novamente a cada 5 segundos para refletir pagamentos
  ou mudanças feitas em outra aba.

Ao finalizar:

- o jogo fica bloqueado para edição;
- o placar final não pode mais ser alterado nem por requisição manual;
- ganhadores pagos aparecem sempre primeiro;
- o filtro Ganhadores é liberado;
- ganhadores recebem e-mail automático.

O e-mail `PalpiteGanhador` é único por pagamento. Se um pagamento vencedor
for aprovado depois de o jogo já estar finalizado, o e-mail também é enviado
automaticamente.

Não é necessária alteração no banco de dados.


## Página inicial somente com Ofertas — v1.5.5

A página pública `index.php` agora mostra somente campanhas de Oferta.

Os formulários de Palpite continuam funcionando normalmente por seus
links diretos (`/palpite/<slug>`), mas não aparecem mais na página inicial.

As Ofertas são exibidas no `index.php` somente quando:

- estão ativas;
- a data/hora inicial já foi alcançada, quando informada;
- a data/hora final ainda não foi alcançada, quando informada.

Assim que `data_fim` é atingida, a Oferta deixa de aparecer na página inicial
e também deixa de ser acessível como Oferta ativa pelo link público.

Quando não houver nenhuma Oferta disponível, a página inicial mostra apenas
a mensagem de que não existem campanhas de oferta abertas.

Não é necessária alteração no banco de dados.


## Correção da barra horizontal — v1.5.6

Corrigida a barra de rolagem horizontal que aparecia nas páginas públicas
de Palpite e Oferta mesmo com o navegador em 100%.

A origem era o campo antispam `website`, que estava invisível mas era
posicionado com `left:-10000px`. O navegador considerava essa posição na
área rolável da página.

O campo agora utiliza `.honeypot-field`, ficando recortado em 1px sem sair
da viewport.

Também foram reforçadas as colunas do Checkout com `min-width:0` e quebra
de textos longos, evitando novos overflows horizontais.

Arquivos alterados:

- `palpite.php`
- `oferta.php`
- `assets/css/app.css`

Não é necessária alteração no banco de dados.


## Correção definitiva do scroll horizontal — v1.5.7

Foi encontrada uma segunda causa para a barra horizontal nas páginas de
Oferta e Palpite.

Além do honeypot corrigido na v1.5.6, os inputs `radio` dos valores e das
formas de pagamento herdavam a regra global `input { width:100% }`.

Esses radios eram invisíveis e `position:absolute`, mas podiam ocupar 100%
da largura da viewport a partir da posição de cada opção, aumentando a largura
real do documento para além da tela.

Na v1.5.7:

- radios ocultos de valores passam a ocupar apenas 1px;
- radios ocultos de formas de pagamento passam a ocupar apenas 1px;
- os labels passam a ser o containing block desses radios;
- o grid do checkout recebe `min-width:0`;
- `html` e `body` impedem overflow horizontal residual.

Não é necessária alteração no banco de dados.


## Pagamentos com sincronização automática — v1.5.8

A tela `Admin > Pagamentos` agora sincroniza os pagamentos com o Asaas
automaticamente, sem recarregar a página.

### Sincronização

- primeira sincronização aproximadamente 1 segundo após abrir a tela;
- novas sincronizações a cada 10 segundos;
- a tabela é atualizada via AJAX;
- os contadores também são atualizados sem refresh;
- existe o botão `Sincronizar agora`;
- quando a aba fica em segundo plano, as consultas automáticas são pausadas;
- cada ciclo consulta um lote pequeno de pagamentos pendentes/vencidos para
  evitar excesso de chamadas à API do Asaas;
- pagamentos sem `asaasPaymentId` também podem ser reconciliados
  automaticamente pela `externalReference`;
- quando um pagamento muda para `Pago`, o fluxo de comprovante e e-mail de
  pagamento aprovado é acionado normalmente.

### Filtros

Foram adicionados os filtros:

- Ano;
- Data;
- Tipo: Oferta ou Palpite.

O filtro de data usa `dataPagamento` quando o pagamento já foi confirmado.
Para cobranças ainda não pagas, usa `criadoEm`.

### Resumo

A tela mostra:

- total de pagamentos;
- pagos;
- pendentes;
- vencidos;
- total recebido.

Não é necessária alteração no banco de dados.


## Categorias de Ofertas — v1.5.9

Toda Oferta agora possui uma categoria obrigatória.

As categorias disponíveis são:

- Local;
- Sinodal;
- Nacional;
- Especial.

Ao criar ou editar uma Oferta em `Admin > Ofertas`, o administrador deve
selecionar a categoria.

A categoria também aparece:

- na listagem administrativa de Ofertas;
- no card da Oferta na página inicial;
- no resumo da página pública da Oferta.

Ofertas existentes são classificadas como `Local` durante a atualização.

### Banco de dados

Em instalações existentes, execute:

`database/migrations/20260809_categorias_ofertas_v1.5.9.sql`

antes de utilizar os arquivos da v1.5.9.


## Encurtador de URLs — v1.6.0

Ofertas e Palpites agora possuem um link curto próprio.

Formato:

`https://checkout.ieclbparobe.com.br/s/xxxxxxxx`

### Regras

- o código curto é único em todo o Checkout;
- o banco possui `UNIQUE KEY` em `links_curtos.codigo`, impedindo repetição
  mesmo em requisições simultâneas;
- cada Oferta ou Palpite possui apenas um link curto estável;
- novos códigos são gerados aleatoriamente com 8 caracteres;
- se ocorrer colisão, o sistema gera outro código automaticamente;
- alterar o título/slug da Oferta ou Palpite não altera o link curto;
- o link curto redireciona para o slug atual do conteúdo;
- links antigos continuam funcionando enquanto a Oferta/Palpite existir.

No Admin, o link curto aparece:

- na edição da Oferta;
- na listagem de Ofertas;
- na edição do Palpite;
- na listagem de Palpites.

Também existe o botão `Copiar link`.

### Registros existentes

A migração cria automaticamente um link curto único para todas as Ofertas e
Palpites que já estavam cadastrados antes da v1.6.0.

### Banco de dados

Execute:

`database/migrations/20260811_encurtador_url_v1.6.0.sql`

antes de utilizar os arquivos da v1.6.0.


## Links curtos alfanuméricos — v1.6.1

Os novos links curtos agora misturam:

- letras maiúsculas;
- letras minúsculas;
- números.

Exemplo:

`https://checkout.ieclbparobe.com.br/s/G7mK4qNp`

Cada novo código de 8 caracteres possui obrigatoriamente pelo menos uma
letra maiúscula, uma letra minúscula e um número.

A coluna `links_curtos.codigo` passou a usar `ascii_bin`, portanto a busca
preserva corretamente maiúsculas e minúsculas. A `UNIQUE KEY` continua
impedindo a repetição de qualquer código.

Links antigos em letras minúsculas continuam funcionando normalmente.

### Banco de dados

Execute:

`database/migrations/20260811_link_curto_alfanumerico_v1.6.1.sql`

antes de utilizar os arquivos da v1.6.1.


## Regeneração dos links curtos existentes — v1.6.2

A v1.6.2 troca também os códigos que já haviam sido criados nas versões
anteriores.

Assim, códigos antigos como:

`o0000002`

`p0000001`

são substituídos por códigos no novo padrão, contendo:

- letra maiúscula;
- letra minúscula;
- número.

A atualização preserva a unicidade global dos links através da
`UNIQUE KEY uq_link_curto_codigo`.

### Atenção

Ao executar esta atualização, os links curtos antigos deixam de funcionar,
pois todos os códigos existentes são substituídos.

Os links completos por slug, como:

`/oferta/<slug>`

e

`/palpite/<slug>`

não são alterados.

### Atualização

Execute:

`database/migrations/20260811_regenerar_links_curtos_v1.6.2.sql`

após já ter aplicado a estrutura do encurtador das versões v1.6.0/v1.6.1.


## Google Analytics 4 — v1.6.3

Foi adicionada uma configuração própria do Google Analytics no Checkout.

Menu:

`Admin > Google Analytics`

O administrador pode:

- informar o Measurement ID no formato `G-XXXXXXXXXX`;
- ativar ou desativar o Analytics sem editar arquivos;
- consultar o status da integração.

Quando ativo, a tag é inserida automaticamente nas páginas públicas:

- página inicial;
- Oferta;
- Palpite;
- Pagamento;
- Comprovante.

O painel administrativo não recebe a tag para evitar misturar acessos
internos do administrador com as estatísticas públicas.

### Funções

Para carregar a tag em uma nova página pública:

`<?= AnalyticsService::renderHead() ?>`

Para eventos personalizados:

`<?= AnalyticsService::renderEvent('nome_do_evento', ['chave' => 'valor']) ?>`

### Banco de dados

Execute:

`database/migrations/20260811_google_analytics_v1.6.3.sql`

antes de utilizar a configuração no Admin.


## Menu hamburger do Admin — v1.6.4

O painel administrativo agora possui menu hamburger.

### Desktop

- o botão hamburger fica no topo do painel;
- ao clicar, a barra lateral é recolhida;
- ao clicar novamente, a barra lateral reaparece;
- a preferência de menu aberto/fechado é salva no navegador.

### Celular e tablet

- a barra lateral começa fechada;
- o hamburger abre o menu como painel lateral;
- é exibido um fundo escuro sobre a página;
- clicar fora do menu, no botão X ou pressionar ESC fecha o menu;
- ao escolher uma opção, o menu também fecha automaticamente.

A alteração vale para todo o painel administrativo, incluindo Dashboard,
Ofertas, Palpites, Pagamentos, Relatórios, Asaas, E-mail e Google Analytics.

Não é necessária alteração no banco de dados.


## Correção do menu hamburger — v1.6.5

A implementação anterior podia carregar o HTML novo junto com uma versão
antiga do CSS em cache. Isso fazia o botão interno aparecer como um pequeno
`X` padrão e deixava o layout do Admin incorreto.

Na v1.6.5:

- o CSS do Admin usa cache busting `app.css?v=1.6.5`;
- foi removido o botão X interno da barra lateral;
- existe apenas o botão hamburger no topo da área administrativa;
- no desktop, o hamburger recolhe e expande a barra lateral;
- no celular/tablet, o mesmo botão abre e fecha o menu lateral;
- clicar no fundo escuro ou pressionar ESC também fecha no mobile;
- regras antigas do CSS foram sobrescritas com seletores específicos do Admin.

Não é necessária alteração no banco de dados.


## Novo index de Ofertas — v1.6.6

A página inicial foi organizada em duas áreas.

### Ofertas do mês

No topo aparecem no máximo 4 Ofertas relacionadas ao mês atual.

Exemplo em agosto:

`Agosto 2026`

São exibidas até quatro campanhas cuja vigência cruza o mês atual e que ainda
não tenham encerrado.

### Próximas ofertas

Logo abaixo aparece a seção:

`Próximas ofertas`

Ela lista campanhas programadas para depois do mês atual, em ordem de data.

Caso ainda não existam campanhas futuras programadas, a seção pode utilizar
outras campanhas ativas que não tenham entrado entre as quatro principais.

Palpites continuam fora do `index.php`.

Ofertas encerradas continuam sem aparecer na página inicial.

Não é necessária alteração no banco de dados.


## Filtro por ano em Ofertas e Palpites — v1.6.7

Foram adicionados filtros por ano nas telas:

- `Admin > Ofertas`;
- `Admin > Palpites`.

O campo `Ano` é carregado automaticamente com os anos que possuem registros.

Ao selecionar um ano, a listagem é atualizada e exibe somente os registros
daquele período.

### Regra do ano

Para Ofertas, o ano é baseado em:

`data_inicio`

e, quando não houver data inicial:

`criadoEm`

Para Palpites, o ano é baseado primeiro em:

`data_jogo`

depois em `data_inicio` e, se necessário, `criadoEm`.

Existe também a opção `Todos os anos` e um botão `Limpar filtro`.

Não é necessária alteração no banco de dados.


## Ofertas anteriores no index — v1.6.8

A página inicial agora possui três grupos de Ofertas:

1. Ofertas do mês;
2. Próximas ofertas;
3. Ofertas anteriores.

### Ofertas anteriores

Quando `data_inicio` de uma Oferta é menor que a data/hora atual, ela deixa
de ser tratada como uma nova Oferta do mês e passa para:

`Ofertas anteriores`

Essa mudança é apenas de organização da página inicial.

Se a Oferta ainda estiver ativa e dentro de sua data final, o link público
continua funcionando normalmente e ainda pode receber pagamentos.

As Ofertas anteriores são ordenadas da mais recente para a mais antiga.

Palpites continuam fora do `index.php`.

Não é necessária alteração no banco de dados.


## Correção da organização do index — v1.6.9

A regra das três seções do `index.php` foi ajustada.

### 1. Ofertas do mês

As quatro primeiras posições mostram Ofertas cuja `data_inicio` pertence
ao mês atual.

Exemplo em agosto:

- 08/08/2026 continua em `Agosto 2026`, mesmo após o dia 8;
- 10/08/2026 continua em `Agosto 2026`;
- 30/08/2026 também aparece em `Agosto 2026`.

São exibidas no máximo quatro, em ordem de `data_inicio`.

### 2. Próximas ofertas

Mostra Ofertas que ainda não começaram (`data_inicio >= NOW()`) e que não
foram usadas nas quatro posições da seção do mês.

Assim podem aparecer:

- Ofertas restantes do próprio mês;
- Ofertas de setembro, outubro e demais meses futuros.

### 3. Ofertas anteriores

Mostra Ofertas cuja `data_inicio` é anterior ao primeiro dia do mês atual.

Exemplo em agosto:

- 01/07/2026 -> `Ofertas anteriores`;
- 08/08/2026 -> continua em `Agosto 2026`, e não em anteriores.

Ofertas já encerradas (`data_fim <= NOW()`) permanecem ocultas do index.

Palpites continuam fora do `index.php`.

Não é necessária alteração no banco de dados.


## Ofertas anteriores paginadas e próximas limitadas — v1.7.0

### Próximas ofertas

A seção `Próximas ofertas` do `index.php` agora mostra somente as 4 próximas
campanhas, em ordem de `data_inicio`.

### Ofertas anteriores

O `index.php` mantém uma prévia das Ofertas anteriores e agora possui o botão:

`Ver todas as ofertas anteriores`

Nova página pública:

`/ofertas-anteriores`

Nela é possível filtrar o histórico por:

- Ano;
- Categoria: Local, Sinodal, Nacional ou Especial.

O histórico possui paginação de 12 Ofertas por página.

Os filtros são preservados ao navegar entre as páginas.

Ofertas anteriores são aquelas cuja `data_inicio` é anterior ao primeiro dia
do mês atual.

Não é necessária alteração no banco de dados.


## Doações antecipadas em Ofertas — v1.7.1

As Ofertas podem receber doações mesmo quando a `data_inicio` ainda não
chegou.

A partir desta versão:

- `data_inicio` serve para organizar a Oferta no `index.php`;
- uma Oferta futura pode ser aberta pelo link normal ou link curto;
- PIX, Cartão e Boleto podem ser utilizados normalmente, quando habilitados;
- o recebimento continua bloqueado quando a Oferta estiver inativa;
- o recebimento é bloqueado quando `data_fim` for atingida.

Na página de uma Oferta futura aparece o aviso:

`Doações antecipadas abertas`

informando a data programada da campanha e que a contribuição já pode ser
realizada.

O comportamento dos Palpites não foi alterado: neles o período de
participação continua sendo respeitado.

Não é necessária alteração no banco de dados.


## Aviso de doação antecipada no resumo — v1.7.2

O aviso `Doações antecipadas abertas` foi movido.

Antes ele aparecia no topo de toda a página da Oferta.

Agora ele aparece no painel lateral `Oferta Selecionada`, logo abaixo do
bloco `Total`.

O aviso continua mostrando:

- a data programada da Oferta;
- que a campanha já pode receber doações;
- que a data inicial serve para organização no calendário.

Não é necessária alteração no banco de dados.


## Resultado público após encerramento do Palpite — v1.7.3

Depois que o período de participação do Palpite termina, o mesmo link público:

`/palpite/<slug>`

deixa de mostrar a tela genérica de indisponibilidade e passa a mostrar uma
tela de resultado.

### Antes do jogo ser finalizado

A página informa:

- que os palpites estão encerrados;
- o placar disponível, quando já houver placar salvo;
- que o resultado final ainda está sendo aguardado.

A página se atualiza automaticamente a cada 30 segundos enquanto aguarda a
finalização.

### Depois do jogo ser finalizado

A página mostra:

- nome do jogo;
- equipes;
- placar final;
- se houve ganhador ou não.

Se houver ganhador, aparece apenas:

`Houve ganhador!`

Se não houver:

`Não houve ganhador.`

Nenhum nome, CPF, telefone, e-mail ou palpite individual é exibido
publicamente.

A regra de ganhador continua sendo: palpite exato do placar final com
pagamento confirmado.

Não é necessária alteração no banco de dados.
