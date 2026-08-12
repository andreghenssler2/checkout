<?php
require_once dirname(__DIR__,2).'/bootstrap.php';Auth::require();
$id=(int)($_GET['id']??$_POST['idOferta']??0);$offer=$id?OfertaRepository::find($id):null;$pageTitle=$id?'Editar oferta':'Nova oferta';$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  if(!Support::checkCsrf($_POST['_csrf']??null))throw new RuntimeException('Sessão expirada.');
  $image=$offer['imagem']??null;
  if(isset($_FILES['imagem']) && $_FILES['imagem']['error']===UPLOAD_ERR_OK){
   if($_FILES['imagem']['size']>5*1024*1024)throw new RuntimeException('A imagem deve ter no máximo 5 MB.');
   $info=new finfo(FILEINFO_MIME_TYPE);$mime=$info->file($_FILES['imagem']['tmp_name']);$map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
   if(!isset($map[$mime]))throw new RuntimeException('Use imagem JPG, PNG ou WEBP.');
   $dir=dirname(__DIR__,2).'/uploads/ofertas';if(!is_dir($dir))mkdir($dir,0755,true);$filename=date('YmdHis').'-'.bin2hex(random_bytes(5)).'.'.$map[$mime];
   if(!move_uploaded_file($_FILES['imagem']['tmp_name'],$dir.'/'.$filename))throw new RuntimeException('Falha ao salvar imagem.');$image='uploads/ofertas/'.$filename;
  }
  $vals=preg_split('/[,;\r\n]+/',(string)($_POST['valores_fixos']??''),-1,PREG_SPLIT_NO_EMPTY)?:[];
  $id=OfertaRepository::save(['idOferta'=>$id,'titulo'=>$_POST['titulo']??'','categoria'=>$_POST['categoria']??'','descricao'=>$_POST['descricao']??'','imagem'=>$image,'data_inicio'=>$_POST['data_inicio']??null,'data_fim'=>$_POST['data_fim']??null,'valor_minimo'=>$_POST['valor_minimo']??10,'permitir_valor_livre'=>$_POST['permitir_valor_livre']??0,'pix_ativo'=>$_POST['pix_ativo']??0,'cartao_ativo'=>$_POST['cartao_ativo']??0,'boleto_ativo'=>$_POST['boleto_ativo']??0,'ativo'=>$_POST['ativo']??0,'valores'=>$vals]);
  Support::flash('success','Oferta salva com sucesso.');Support::redirect('/admin/ofertas/form.php?id='.$id);
 }catch(Throwable $e){$error=$e->getMessage();}
}
$offer=$id?OfertaRepository::find($id):$offer;
$values=$id?array_column(OfertaRepository::values($id),'valor'):[];
$shortUrl=$id
    ? ShortUrlService::urlFor(
        ShortUrlService::TYPE_OFFER,
        $id
    )
    : null;
require dirname(__DIR__).'/_header.php';?><?php if($error):?><div class="alert error"><?= Support::e($error) ?></div><?php endif;?>
<?php if($shortUrl):?>
<div class="panel short-url-panel">
    <div>
        <span class="content-type-badge">Link curto</span>
        <h2>URL encurtada da Oferta</h2>
        <p>Este endereço é único e continuará funcionando mesmo se o título da Oferta for alterado.</p>
    </div>
    <div class="short-url-control">
        <input type="text" value="<?= Support::e($shortUrl) ?>" readonly>
        <button class="btn" type="button" data-copy-url="<?= Support::e($shortUrl) ?>">Copiar link</button>
        <a class="btn primary" href="<?= Support::e($shortUrl) ?>" target="_blank">Abrir</a>
    </div>
</div>
<?php endif;?>
<form method="post" enctype="multipart/form-data" class="panel"><input type="hidden" name="_csrf" value="<?= Support::csrf() ?>"><input type="hidden" name="idOferta" value="<?= $id ?>"><div class="grid2"><label class="full">Título*<input name="titulo" value="<?= Support::e($offer['titulo']??'') ?>" required><small>O endereço será gerado automaticamente pelo nome da oferta.</small></label><label>Categoria*<select name="categoria" required><option value="">Selecione a categoria</option><?php foreach(OfertaRepository::categories() as $categoria):?><option value="<?= Support::e($categoria) ?>" <?= ($offer['categoria']??'')===$categoria?'selected':'' ?>><?= Support::e($categoria) ?></option><?php endforeach;?></select><small>Classifique a oferta como Local, Sinodal, Nacional ou Especial.</small></label><div></div><label class="full">Descrição<textarea name="descricao" rows="4"><?= Support::e($offer['descricao']??'') ?></textarea></label><label>Início<input type="datetime-local" name="data_inicio" value="<?= !empty($offer['data_inicio'])?Support::e(str_replace(' ','T',substr($offer['data_inicio'],0,16))):'' ?>"></label><label>Fim<input type="datetime-local" name="data_fim" value="<?= !empty($offer['data_fim'])?Support::e(str_replace(' ','T',substr($offer['data_fim'],0,16))):'' ?>"></label><label>Valores fixos*<input name="valores_fixos" value="<?= Support::e(implode(', ',array_map(fn($v)=>number_format((float)$v,2,',',''),$values))) ?>" placeholder="10, 20, 30, 50, 100"><small>Separe por vírgula. Valores abaixo de R$ 10 são ignorados.</small></label><label>Valor mínimo<input type="number" step="0.01" min="10" name="valor_minimo" value="<?= Support::e($offer['valor_minimo']??'10.00') ?>" required></label><label class="full">Imagem<input type="file" name="imagem" accept="image/jpeg,image/png,image/webp"></label></div><div class="checks"><label><input type="checkbox" name="permitir_valor_livre" value="1" <?= !isset($offer['permitir_valor_livre'])||$offer['permitir_valor_livre']?'checked':'' ?>> Permitir outro valor</label><label><input type="checkbox" name="pix_ativo" value="1" <?= !isset($offer['pix_ativo'])||$offer['pix_ativo']?'checked':'' ?>> PIX</label><label><input type="checkbox" name="cartao_ativo" value="1" <?= !isset($offer['cartao_ativo'])||$offer['cartao_ativo']?'checked':'' ?>> Cartão de Crédito</label><label><input type="checkbox" name="boleto_ativo" value="1" <?= !empty($offer['boleto_ativo'])?'checked':'' ?>> Boleto</label><label><input type="checkbox" name="ativo" value="1" <?= !isset($offer['ativo'])||$offer['ativo']?'checked':'' ?>> Oferta ativa</label></div>
<div class="alert muted boleto-admin-note">
    <strong>Regra do Boleto:</strong>
    o vencimento será de 1 dia útil. Se cair em sábado ou domingo,
    será levado para o próximo dia útil. O boleto não será exibido
    quando o vencimento ficar no mesmo dia ou após o encerramento da oferta.
    Boleto não é permitido nos formulários de Palpite.
</div>
<button class="btn primary" type="submit">Salvar oferta</button></form><?php require dirname(__DIR__).'/_footer.php';
