<?php
/**
 * Outlook Autodiscover for mzanzisave.co.za mailboxes (HostAfrica / DirectAdmin).
 * Outlook posts the email address it is setting up; we answer with the
 * IMAP and SMTP settings so the user only types their address and password.
 */

const DOMAIN      = 'mzanzisave.co.za';
const MAIL_SERVER = 'mail.mzanzisave.co.za';

// Read the address Outlook is asking about (POST body), if any.
$email = '';
$raw = file_get_contents('php://input');
if ($raw && preg_match('#<EMailAddress>\s*([^<\s]+)\s*</EMailAddress>#i', $raw, $m)) {
    $email = strtolower(trim($m[1]));
}
// Only describe mailboxes on our own domain.
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || substr($email, -strlen('@' . DOMAIN)) !== '@' . DOMAIN) {
    $email = '';
}
$login  = htmlspecialchars($email, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$server = MAIL_SERVER;

header('Content-Type: application/xml; charset=utf-8');
echo <<<XML
<?xml version="1.0" encoding="utf-8"?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
  <Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a">
    <Account>
      <AccountType>email</AccountType>
      <Action>settings</Action>
      <Protocol>
        <Type>IMAP</Type>
        <Server>{$server}</Server>
        <Port>993</Port>
        <DomainRequired>off</DomainRequired>
        <LoginName>{$login}</LoginName>
        <SPA>off</SPA>
        <SSL>on</SSL>
        <AuthRequired>on</AuthRequired>
      </Protocol>
      <Protocol>
        <Type>SMTP</Type>
        <Server>{$server}</Server>
        <Port>465</Port>
        <DomainRequired>off</DomainRequired>
        <LoginName>{$login}</LoginName>
        <SPA>off</SPA>
        <Encryption>SSL</Encryption>
        <AuthRequired>on</AuthRequired>
        <UsePOPAuth>on</UsePOPAuth>
      </Protocol>
    </Account>
  </Response>
</Autodiscover>
XML;
