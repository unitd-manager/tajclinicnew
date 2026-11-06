<?php 
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$fn = Zend_Registry::get('fn');
		$tv = Zend_Registry::get('tv');
		$cpCfg = Zend_Registry::get('cpCfg');

		$images  = '<img src="images/logo.png"  width="70px" height="75px"/>';
		$images2 = '<img src="images/ISO-Logo.png"  width="58px" height="65px"/>';
		$images3 = '<img src="images/nbhs.jpg"  width="66px" height="65px"/>';

    	$header='
		<table border="0" width="100%" cellpadding="4" style="border-bottom:2px solid #000000;">
            <tr>
                <td width="10%">'.$images.'</td>
                <td width="70%" align="center"><br/>
                    <span style="font-size:30px;color:#800000;"><b>'.$cpCfg['cp.companyName'].'</b></span><br/>
                    <span style="font-size:11px;font-weight:bold;">'.$cpCfg['cp.addressPdf1'].'<br/>
                    '.$cpCfg['cp.addressPdf2'].'<br/>
                    '.$cpCfg['cp.addressPdf3'].' &nbsp; '.$cpCfg['cp.addressPdf4'].'<br/>
                    '.$cpCfg['cp.addressPdf5'].'</span>
                </td>
                <td width="9%" align="right">'.$images2.'</td>
                <td width="11%" align="right">'.$images3.'</td>

            </tr>
        </table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(40);

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

		$this->writeHTML($footer, true, false, false, false, '');
		//$this->SetFooterMargin(80);
    }
}
?>