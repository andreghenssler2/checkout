<?php
require_once dirname(__DIR__) . '/bootstrap.php';

$errors=[];$success=false;$requirements=[
 'PHP 8.1+' => PHP_VERSION_ID >= 80100,
 'PDO MySQL' => extension_loaded('pdo_mysql'),
 'cURL' => extension_loaded('curl'),
 'OpenSSL' => extension_loaded('openssl'),
 'Fileinfo' => extension_loaded('fileinfo'),
 'Mbstring' => extension_loaded('mbstring'),
];

if (is_file(dirname(__DIR__).'/config/database.php')) {
    try { if ((int)Database::connection()->query('SELECT COUNT(*) FROM administradores')->fetchColumn() > 0) { http_response_code(403); die('Instalação já concluída. Remova a pasta /install após confirmar o funcionamento.'); } } catch(Throwable) {}
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (in_array(false,$requirements,true)) $errors[]='O servidor não atende aos requisitos mínimos.';
    $host=trim($_POST['db_host']??'localhost');$name=trim($_POST['db_name']??'');$user=trim($_POST['db_user']??'');$pass=(string)($_POST['db_pass']??'');
    $adminName=trim($_POST['admin_nome']??'');$email=strtolower(trim($_POST['admin_email']??''));$adminPass=(string)($_POST['admin_senha']??'');
    if($name===''||$user==='')$errors[]='Informe o banco e o usuário MySQL.';
    if($adminName===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($adminPass)<8)$errors[]='Informe administrador, e-mail válido e senha com no mínimo 8 caracteres.';
    if(!$errors){
        try{
            $pdo=new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
            $sql=file_get_contents(dirname(__DIR__).'/database/schema.sql');$pdo->exec((string)$sql);
            $pdo->prepare('INSERT INTO administradores (nome,email,senha,ativo) VALUES (:n,:e,:s,1)')->execute([':n'=>$adminName,':e'=>$email,':s'=>password_hash($adminPass,PASSWORD_DEFAULT)]);
            $key=bin2hex(random_bytes(32));
            $config="<?php\n".
                "define('DB_HOST', ".var_export($host,true).");\n".
                "define('DB_NAME', ".var_export($name,true).");\n".
                "define('DB_USER', ".var_export($user,true).");\n".
                "define('DB_PASS', ".var_export($pass,true).");\n".
                "define('APP_KEY', ".var_export($key,true).");\n";
            if(file_put_contents(dirname(__DIR__).'/config/database.php',$config)===false) throw new RuntimeException('Não foi possível gravar config/database.php. Verifique a permissão da pasta config.');
            $success=true;
        }catch(Throwable $e){$errors[]=$e->getMessage();}
    }
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Instalar - Checkout</title><link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css"></head><body class="bg"><main class="install"><div class="panel"><h1>Instalação — Checkout</h1><p>Aplicação independente em <strong>checkout.ieclbparobe.com.br</strong>.</p><?php foreach($requirements as $label=>$ok):?><div class="req <?= $ok?'ok':'bad' ?>"><?= Support::e($label) ?>: <?= $ok?'OK':'não disponível' ?></div><?php endforeach;?><?php foreach($errors as $e):?><div class="alert error"><?= Support::e($e) ?></div><?php endforeach;?><?php if($success):?><div class="alert success">Instalação concluída. Por segurança, remova a pasta <code>install</code>.</div><a class="btn primary" href="<?= APP_URL ?>/admin/">Ir para o administrador</a><?php else:?><form method="post"><h2>Banco de dados</h2><div class="grid2"><label>Host<input name="db_host" value="<?= Support::e($_POST['db_host']??'localhost') ?>" required></label><label>Banco<input name="db_name" value="<?= Support::e($_POST['db_name']??'') ?>" required></label><label>Usuário<input name="db_user" value="<?= Support::e($_POST['db_user']??'') ?>" required></label><label>Senha<input type="password" name="db_pass"></label></div><h2>Administrador</h2><div class="grid2"><label>Nome<input name="admin_nome" required></label><label>E-mail<input type="email" name="admin_email" required></label><label class="full">Senha (mín. 8 caracteres)<input type="password" name="admin_senha" minlength="8" required></label></div><button class="btn primary" type="submit">Instalar aplicação</button></form><?php endif;?></div></main></body></html>
