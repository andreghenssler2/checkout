<?php
require_once __DIR__.'/bootstrap.php';header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');$p=PagamentoRepository::byCode(trim((string)($_GET['codigo']??'')));if(!$p){http_response_code(404);echo json_encode(['error'=>'not_found']);exit;}echo json_encode(['status'=>$p['status']],JSON_UNESCAPED_UNICODE);
