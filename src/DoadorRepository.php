<?php

declare(strict_types=1);

final class DoadorRepository
{
    public static function upsert(array $d): array
    {
        $cpf=Support::cpfDigits((string)$d['cpf']);
        $db=Database::connection();
        $st=$db->prepare('SELECT * FROM doadores WHERE cpf=:cpf LIMIT 1');$st->execute([':cpf'=>$cpf]);$row=$st->fetch();
        if($row){
            $db->prepare('UPDATE doadores SET nome=:n,email=:e,telefone=:t WHERE idDoador=:id')->execute([':n'=>$d['nome'],':e'=>$d['email'],':t'=>$d['telefone'],':id'=>$row['idDoador']]);
            $row['nome']=$d['nome'];$row['email']=$d['email'];$row['telefone']=$d['telefone'];return $row;
        }
        $db->prepare('INSERT INTO doadores (nome,cpf,email,telefone) VALUES (:n,:c,:e,:t)')->execute([':n'=>$d['nome'],':c'=>$cpf,':e'=>$d['email'],':t'=>$d['telefone']]);
        return ['idDoador'=>(int)$db->lastInsertId(),'nome'=>$d['nome'],'cpf'=>$cpf,'email'=>$d['email'],'telefone'=>$d['telefone'],'asaasCustomerId'=>null];
    }
    public static function setAsaas(int $id,string $asaas): void { Database::connection()->prepare('UPDATE doadores SET asaasCustomerId=:a WHERE idDoador=:id')->execute([':a'=>$asaas,':id'=>$id]); }
}
