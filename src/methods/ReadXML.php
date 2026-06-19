<?php
// I validate to prevent errors bc different localhost between React, PHP and Database.
$allowedOrigins = [
    "http://localhost:5173",
    "http://127.0.0.1:5173"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// I load my database config.
require "../db/database.php";

if (isset($_FILES['xml'])) {

    $tmp = $_FILES['xml']['tmp_name'];

    $xml = simplexml_load_file($tmp);
    $attrs = $xml->attributes();

    // print_r($attrs);

    // PROVEEDORES
    $emisor = $xml->xpath('//cfdi:Emisor');

    $rfc_emisor = (string)$emisor[0]['Rfc'];
    $nombre_emisor = (string)$emisor[0]['Nombre'];
    $regimen_emisor = (string)$emisor[0]['RegimenFiscal'];

    $stmt = $pdo->prepare("SELECT 1 FROM proveedores WHERE rfc = :rfc;");
    $stmt->execute([':rfc' => $rfc_emisor]);

    $cliente_exists = $stmt->fetch();

    if (!$cliente_exists) {
        $stmt = $pdo->prepare("
            INSERT INTO proveedores (rfc, nombre, regimen_fiscal)
            VALUES (:rfc_emisor, :nombre_emisor, :regimen_emisor);
        ");
    
        $stmt->execute([
            ':rfc_emisor' => $rfc_emisor,
            ':nombre_emisor' => $nombre_emisor,
            ':regimen_emisor' => $regimen_emisor
        ]);
    }

    // CLIENTES
    $receptor = $xml->xpath('//cfdi:Receptor');

    $rfc_receptor = (string)$receptor[0]['Rfc'];
    $nombre_receptor = (string)$receptor[0]['Nombre'];
    $domicilio_fiscal = (string)$receptor[0]['DomicilioFiscalReceptor'];
    $regimen_receptor = (string)$receptor[0]['RegimenFiscalReceptor'];

    $stmt = $pdo->prepare("SELECT 1 FROM clientes WHERE rfc = :rfc;");
    $stmt->execute([':rfc' => $rfc_receptor]);

    $proveedor_exists = $stmt->fetch();

    if (!$proveedor_exists) {
        $stmt = $pdo->prepare("
            INSERT INTO clientes (rfc, nombre, domicilio_fiscal, regimen_fiscal)
            VALUES (:rfc_receptor, :nombre_receptor, :domicilio_receptor, :regimen_receptor);
        ");
    
        $stmt->execute([
            ':rfc_receptor' => $rfc_receptor,
            ':nombre_receptor' => $nombre_receptor,
            ':domicilio_receptor' => $domicilio_fiscal,
            ':regimen_receptor' => $regimen_receptor
        ]);
    }

    // CFDI
    $version_cfdi = $attrs['Version'];
    $serie_cfdi = $attrs['Serie'];
    $folio_cfdi = $attrs['Folio'];
    $fecha_cfdi = $attrs['Fecha'];
    $forma_pago = $attrs['FormaPago'];
    $condiciones_pago = $attrs['CondicionesDePago'];
    $subtotal = $attrs['SubTotal'];
    $moneda = $attrs['Moneda'];
    $tipo_cfdi = $attrs['TipoDeComprobante'];
    $exportacion = $attrs['Exportacion'];
    $sello = $attrs['Sello'];
    $metodo_pago = $attrs['MetodoPago'];
    $lugar_exp = $attrs['LugarExpedicion'];
    $uso_cfdi = $attrs['UsoCFDI'];


    // print_r($emisor);

    echo json_encode([
        'success' => true
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => 'No XML file received'
    ]);
}