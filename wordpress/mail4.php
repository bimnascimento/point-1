<?php

echo '<br/>';
echo '----------------------------';
echo '<br/>';
echo ' - 587';
echo '<br/>';

//ini_set( 'display_errors', 1 );
//error_reporting( E_ALL );
require 'doc/phpmailer/class.phpmailer.php';         // https://github.com/PHPMailer/PHPMailer

$mail = new PHPMailer;
$mail->setLanguage('br');                             // Habilita as saídas de erro em Português
$mail->CharSet='UTF-8';                               // Habilita o envio do email como 'UTF-8'

$mail->SMTPDebug = 2;                               // Habilita a saída do tipo "verbose"

$mail->isSMTP();                                      // Configura o disparo como SMTP
$mail->Host = 'email-ssl.com.br';                        // Especifica o enderço do servidor SMTP da Locaweb
$mail->SMTPAuth = true;                               // Habilita a autenticação SMTP
$mail->Username = 'contato@pointlave.com.br';                        // Usuário do SMTP
$mail->Password = '@Pointlave2018';                        // Senha do SMTP
$mail->SMTPSecure = '';                            // Habilita criptografia TLS | 'ssl' também é possível
$mail->Port = 587;                                    // Porta TCP para a conexão


$mail->From = 'contato@pointlave.com.br';                          // Endereço previamente verificado no painel do SMTP
$mail->FromName = 'SMTP Locaweb';                     // Nome no remetente
$mail->addAddress('boxclickjf@gmail.com', 'boxclickjf');// Acrescente um destinatário
//$mail->addAddress('ao@exemplo.com');                // O nome é opcional
//$mail->addReplyTo('info@exemplo.com', 'Informação');
//$mail->addCC('cc@exemplo.com');
//$mail->addBCC('bcc@exemplo.com');

$mail->isHTML(true);                                  // Configura o formato do email como HTML

$mail->Subject = 'Aqui o assunto da mensagem';
$mail->Body    = 'Esse é o body de uma mensagem HTML <strong>em negrito!</strong>';
$mail->AltBody = 'Esse é o corpo da mensagem em formato "plain text" para clientes de email não-HTML';

if(!$mail->send()) {
    echo 'A mensagem não pode ser enviada';
    echo 'Mensagem de erro: ' . $mail->ErrorInfo;
} else {
    echo 'Mensagem enviada com sucesso';
}
