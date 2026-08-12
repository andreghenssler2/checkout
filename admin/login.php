<?php
require_once dirname(__DIR__) . '/bootstrap.php';
if(Auth::check()) Support::redirect('/admin/');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!Support::checkCsrf($_POST['_csrf']??null))$error='Sessão expirada. Tente novamente.';
 elseif(Auth::attempt((string)($_POST['email']??''),(string)($_POST['senha']??'')))Support::redirect('/admin/');
 else $error='E-mail ou senha inválidos.';
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Entrar - Checkout</title><link rel="stylesheet" href="<?= APP_URL ?>/assets/css/app.css"></head><body class="bg"><main class="login-card"><div class="panel"><h1>Checkout</h1><p>Administração</p><?php if($error):?><div class="alert error"><?= Support::e($error) ?></div><?php endif;?><form method="post"><input type="hidden" name="_csrf" value="<?= Support::csrf() ?>"><label>E-mail<input type="email" name="email" autocomplete="username" required></label><label>Senha<input type="password" name="senha" autocomplete="current-password" required></label><button class="btn primary fullbtn">Entrar</button></form></div></main></body></html>
