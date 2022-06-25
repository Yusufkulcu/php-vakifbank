<?php

namespace Yufusphp\Vakifbank;

use SimpleXMLElement;

class Vakifbank
{

    protected $MerchantId;
    protected $MerchantPassword;
    protected $TerminalNo;
    protected $OrderId;
    protected $CardNumber;
    protected $ExpiryDate;
    protected $PurchaseAmount;
    protected $Currency;
    protected $BrandName;
    protected $SuccessUrl;
    protected $FailureUrl;
    protected $Type;

    public function setMerchantId($MerchantId): Vakifbank
    {
        $this->MerchantId = $MerchantId;

        return $this;
    }

    public function setMerchantPassword($MerchantPassword): Vakifbank
    {
        $this->MerchantPassword = $MerchantPassword;

        return $this;
    }

    public function setTerminalNo($TerminalNo): Vakifbank
    {
        $this->TerminalNo = $TerminalNo;

        return $this;
    }

    public function setOrderId($OrderId): Vakifbank
    {
        $this->OrderId = $OrderId;

        return $this;
    }

    public function setCardNumber($CardNumber): Vakifbank
    {
        $this->CardNumber = $CardNumber;

        return $this;
    }

    public function setExpiryDate($ExpiryDate): Vakifbank
    {
        $this->ExpiryDate = $ExpiryDate;

        return $this;
    }

    public function setPurchaseAmount($PurchaseAmount): Vakifbank
    {
        $this->PurchaseAmount = $PurchaseAmount;

        return $this;
    }

    public function setCurrency($Currency): Vakifbank
    {
        $this->Currency = $Currency;

        return $this;
    }

    public function setBrandName($BrandName): Vakifbank
    {
        $this->BrandName = $BrandName;

        return $this;
    }

    public function setSuccessUrl($SuccessUrl): Vakifbank
    {
        $this->SuccessUrl = $SuccessUrl;

        return $this;
    }

    public function setFailureUrl($FailureUrl): Vakifbank
    {
        $this->FailureUrl = $FailureUrl;

        return $this;
    }

    public function setType($type): Vakifbank
    {
        $this->Type = $type;
        return $this;
    }

    public function check()
    {
        if (empty($this->MerchantId) || empty($this->MerchantPassword) || empty($this->TerminalNo) || empty($this->OrderId) || empty($this->CardNumber) || empty($this->ExpiryDate) || empty($this->PurchaseAmount) || empty($this->Currency) || empty($this->BrandName) || empty($this->SuccessUrl) || empty($this->FailureUrl)) {
            return ["type" => "error", "text" => "Lütfen zorunlu tüm parametreleri eksiksiz gönderiniz."];
        } else {
            $data = [
                "MerchantId" => $this->MerchantId,
                "MerchantPassword" => $this->MerchantPassword,
                "VerifyEnrollmentRequestId" => $this->OrderId,
                "Pan" => $this->CardNumber,
                "ExpiryDate" => $this->ExpiryDate,
                "PurchaseAmount" => $this->PurchaseAmount,
                "Currency" => $this->Currency,
                "BrandName" => $this->BrandName,
                "SuccessUrl" => $this->SuccessUrl,
                "FailureUrl" => $this->FailureUrl,
            ];

            if ($this->Type == "test") {
                $url = "https://3dsecuretest.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx";
            } else {
                $url = "https://3dsecure.vakifbank.com.tr:4443/MPIAPI/MPI_Enrollment.aspx";
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 100);
            $result = @curl_exec($ch);

            $result = simplexml_load_string($result);
            $result = json_encode($result);
            $result = json_decode($result, true);

            if ($result['Message']['VERes']['Status']=="Y") { ?>
                <form name="downloadForm" action="<?php echo $result['Message']['VERes']['ACSUrl'] ?>" method="POST">
                    <input type="hidden" name="PaReq" value="<?php echo $result['Message']['VERes']['PaReq'] ?>">
                    <input type="hidden" name="TermUrl" value="<?php echo $result['Message']['VERes']['TermUrl'] ?>">
                    <input type="hidden" name="MD" value="<?php echo $result['Message']['VERes']['MD'] ?>">
                </form>
                <script language="JavaScript"> document.downloadForm.submit();</script>
            <?php }else {
                return ["type" => "error", "text" => "Hatalı Kart Bilgileri"];
            }

        }
    }

    public function array_to_xml(array $arr, SimpleXMLElement $xml) {
        foreach ($arr as $k => $v) {
            is_array($v)
                ? $this->array_to_xml($v, $xml->addChild($k))
                : $xml->addChild($k, $v);
        }
        return $xml;
    }

    public function getPayment() {
        global $_POST;
        $data = [
            'MerchantId'              => $this->MerchantId,
            'Password'                => $this->MerchantPassword,
            'TerminalNo'              => $this->TerminalNo,
            'TransactionType'         => 'Sale',
            'TransactionId'           => $_POST['VerifyEnrollmentRequestId'],
            'ECI'                     => $_POST['Eci'],
            'CAVV'                    => $_POST['Cavv'],
            'MpiTransactionId'        => $_POST['VerifyEnrollmentRequestId'],
            'OrderId'                 => $_POST['VerifyEnrollmentRequestId'],
            'ClientIp'                =>"37.154.50.186",
            'TransactionDeviceSource' => 0,
        ];

        if ($this->Type == "test") {
            $url = "https://onlineodemetest.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx";
        } else {
            $url = "https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx";
        }

        $data = $this->array_to_xml($data, new SimpleXMLElement('<VposRequest/>'))->asXML();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'prmstr=' . $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_OPTIONS, ["CURLOPT_SSLVERSION" => "CURL_SSLVERSION_TLSv1_1"]);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = simplexml_load_string($result);
        $result = json_encode($result);
        $result = json_decode($result, true);

        return $result;

    }

}