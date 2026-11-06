<?php 
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$fn = Zend_Registry::get('fn');
		$cpCfg = Zend_Registry::get('cpCfg');
		$clinicname = '';
		$drspecialist = '';
		$phone = '';

		$images = '<img src="images/taj-logo-new.png"  width="70px"/>';
		$images2 = '<img src="images/taj-logo1.png"  width="60px"/>';

		$header='
		<table border="0" width="100%" cellpadding="4" style="border-bottom:2px solid #000000;">
            <tr>
                <td width="20%">
                '.$images.'
                </td>
                <td width="65%" align="center"><br/>
                    <span style="font-size:22px;color:#14652b;"><b>'.$cpCfg['cp.companyName'].'</b></span><br/>
                    <span style="font-size:11px;font-weight:bold;color:#14652b;">'.$cpCfg['cp.addressPdf1'].'<br/>
                    '.$cpCfg['cp.addressPdf2'].'<br/>
                    '.$cpCfg['cp.addressPdf3'].'<br/>
                    '.$cpCfg['cp.addressPdf4'].' &nbsp; '.$cpCfg['cp.addressPdf5'].'</span>
                </td>
                <td width="15%">
                '.$images2.'
                </td>
            </tr>
        </table>
		';

		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(32);
	}

	public function Footer() {
		$fn = Zend_Registry::get('fn');
		$cpCfg = Zend_Registry::get('cpCfg');
		
      	$footer='
      	<table border="0" width="100%" style="border-top:1px solid #000000;">
	        <tr>
	            <td align="center">'.$cpCfg['cp.addressPharmacyPdf3'].' '.$cpCfg['cp.addressPharmacyPdf4'].'</td>
	        </tr>
		</table>';
		//$this->writeHTML($footer, true, false, false, false, '');
		//$this->SetFooterMargin(10);
    }
}
?>