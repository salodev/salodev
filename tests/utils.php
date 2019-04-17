#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');

$dn = array(
    "countryName" => "UK",
    "stateOrProvinceName" => "Somerset",
    "localityName" => "Glastonbury",
    "organizationName" => "The Brain Room Limited",
    "organizationalUnitName" => "PHP Documentation Team",
    "commonName" => "Wez Furlong",
    "emailAddress" => "wez@example.com"
);

// Generar una nueva pareja de clave privada (y pública)
$privkey = openssl_pkey_new();

// Generar una petición de firma de certificado
$csr = openssl_csr_new($dn, $privkey);

$sscert = openssl_csr_sign($csr, null, $privkey, 365);

print_r($csr);
print_r(get_resource_type($sscert));

// Ahora querrá preservar su clave privada, CSR y certificado
// autofirmado por lo que pueden ser instalados en su servidor web, servidor de correo
// o cliente de correo (dependiendo del uso previsto para el certificado).
// Este ejemplo muestra cómo obtener estas cosas con variables, pero
// también puede almacenarlas directamente en archivos.
// Normalmente, enviará la CSR a su AC, la cuál se la emitirá después
// con el certificado "real".
openssl_csr_export($csr, $csrout) and var_dump($csrout);
openssl_x509_export($sscert, $certout) and var_dump($certout);
openssl_pkey_export($privkey, $pkeyout, "mypassword") and var_dump($pkeyout);