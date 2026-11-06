<?php 
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$fn = Zend_Registry::get('fn');
		$tv = Zend_Registry::get('tv');
		$cpCfg = Zend_Registry::get('cpCfg');

		$images  = '<img src="images/logo-black.png"  width="70px" height="75px"/>';
		$images2 = '<img src="images/ISO-Logo-black.png"  width="58px" height="65px"/>';

    	$header='
		<table border="0" width="100%" cellpadding="4" style="border-bottom:2px solid #000000;">
            <tr>
                <td width="14%">'.$images.'</td>
                <td width="73%" align="center"><br/>
                    <span style="font-size:20px;"><b>'.$cpCfg['cp.companyName'].'</b></span><br/>
                    <span style="font-size:10px;font-weight:bold;">'.$cpCfg['cp.addressPdf1'].'</span><br/>
                    <span style="font-size:9px;font-weight:bold;">'.$cpCfg['cp.addressPdf2'].'<br/>
                    '.$cpCfg['cp.addressPdf3'].' &nbsp; '.$cpCfg['cp.addressPdf4'].'<br/>
                    '.$cpCfg['cp.addressPdf5'].'</span>
                </td>
                <td width="12%">'.$images2.'</td>
            </tr>
        </table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(37);
	}

	public function Footer() {
		$fn = Zend_Registry::get('fn');
		$cpCfg = Zend_Registry::get('cpCfg');

      	// Page number
      	//$this->Cell(0, 10, '(This is computer generated document, and does not require a signature) Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'R');
		//$font = $this->addTTFfont("lib/calibri.ttf");
        //$this->SetFont($font,'',10);
        	        	//<td></td>

      	$footer ='
      	<table border="0" width="100%" cellpadding="4" style="font-size:13px;">
	        <tr>
				<td align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
	        </tr>
		</table>';

		//$this->writeHTML($footer, true, false, false, false, '');
		//$this->SetFooterMargin(80);
    }
}
?>