<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$fn    = Zend_Registry::get('fn');
		$db    = Zend_Registry::get('db');

		$site_id = $fn->getSessionParam('cp_site_id');

		if (count($this->pages) == 1 ) {
			$images = '<img src="images/logo.png"  width="70px" height="75px"/>';
			$images2 = '<img src="images/ISO-Logo.png"  width="58px" height="65px"/>';

			$header='
			<table border="0" width="100%" cellpadding="4" style="border-bottom:2px solid #000000;">
	            <tr>
	                <td width="12%">'.$images.'</td>
	                <td width="76%" align="center"><br/>
	                    <span style="font-size:23px;color:#800000;"><b>'.$cpCfg['cp.companyName'].'</b></span><br/>
	                    <span style="font-size:11px;font-weight:bold;">'.$cpCfg['cp.addressPdf1'].'<br/>
	                    '.$cpCfg['cp.addressPdf2'].'<br/>
	                    '.$cpCfg['cp.addressPdf3'].'<br/>
	                    '.$cpCfg['cp.addressPdf4'].' &nbsp; '.$cpCfg['cp.addressPdf5'].'</span>
	                </td>
	                <td width="12%" align="right">'.$images2.'</td>
	            </tr>
	        </table>
			';

			$this->writeHTML($header, true, false, false, false, '');
			$this->SetTopMargin(40);
		} else {
			$this->SetTopMargin(6);
		}
	}

	public function Footer() {
		$this->SetFont('Courier','',9);
		$cpCfg = Zend_Registry::get('cpCfg');

      	$footer='
      	<table border="0" width="100%">
	        <tr>
	            <td align="center">
	            </td>
	        </tr>
			<tr>
				<td width="78%">(This is computer generated document, and does not require a signature)</td>
				<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
			</tr>
		</table>';

		$this->writeHTML($footer, true, false, false, false, '');
    }
}
?>