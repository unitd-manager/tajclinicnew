<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$this->SetFont('Courier','B',8);

		if (count($this->pages) == 1) {
			$header='<table border="0" width="100%" style="border-bottom:2px solid #000000;">';
			$header= $header.'
			<tr>
				<td style="font-size:12px" align="center">'.$cpCfg['cp.companyName'].'</td>
			</tr>
			<tr>
				<td align="center">
					'.$cpCfg['cp.addressPdf1'].'<br/>
					'.$cpCfg['cp.addressPdf2'].'
					'.$cpCfg['cp.addressPdf3'].'
				</td>
			</tr>
			<tr>
				<td align="center">
					'.$cpCfg['cp.addressPdf5'].'<br/>
					'.$cpCfg['cp.mobileNumInBill'].'
				</td>
			</tr>

            <tr>
                <td width="100%" align="center"><br/>
                    <span style="font-size:18px;"><b>'.$cpCfg['cp.companyName'] .'</b></span><br/>
                    <span style="font-size:16px;">'.$cpCfg['cp.doctorNamePDF'] .'<br/></span>
                    <span style="font-size:10px;">'.$cpCfg['cp.phonePDF'] .'<br/></span>
                </td>
            </tr>
			';

			$header=$header.'</table>';
			
			$this->writeHTML($header, true, false, false, false, '');
			$this->SetTopMargin(0);
		} else {
			$header='<table border="0" width="100%">';
			$header= $header.'
			<tr>
				<td style="font-size:12px" align="center">'.count($this->pages).'</td>
			</tr>
			<tr>
				<td align="center">TEST
				</td>
			</tr>
			';

			$header=$header.'</table>';
			
			$this->writeHTML($header, true, false, false, false, '');
			$this->SetTopMargin(1);		
		}
	}

	public function Footer() {
		$this->SetFont('Courier','B',8);
      	$footer='<table border="0" width="100%">';
      	
		$footer= $footer.'
		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td width="78%">(This is computer generated document, and does not require a signature)</td>
			<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
		</tr>';
		$footer=$footer.'</table>';
		$this->writeHTML($footer, true, false, false, false, '');
		$this->SetFooterMargin(1);
    }
}
?>