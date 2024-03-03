<?php
namespace mvnrsa\FlexibleReports;

use HeadlessChromium\BrowserFactory;
use Illuminate\Support\Str;

class pdfUtils
{
	function fromUrl($url,$pdf_file='',$landscape=false)
	{
		if (file_exists('/usr/bin/chromium-browser'))
			$browserFactory = new BrowserFactory('/usr/bin/chromium-browser');
		elseif (file_exists('/usr/bin/chromium'))
			$browserFactory = new BrowserFactory('/usr/bin/chromium');
		/* elseif (file_exists('/snap/bin/chromium'))
			$browserFactory = new BrowserFactory('/snap/bin/chromium'); */
		elseif (file_exists('/usr/bin/google-chrome'))
			$browserFactory = new BrowserFactory('/usr/bin/google-chrome');
		else
			return false;

		if (empty($pdf_file))
		{
			if (!is_dir(public_path("tmp")))
				mkdir(public_path("tmp"));
			$pdf_file = public_path("tmp/" . Str::uuid() . ".pdf");
		}

		// Width and Height - A4
		$width=8.3;
		$height=11.7;
		if ($landscape)
		{
			$width=11.7;
			$height=8.3;
		}
	
	    // starts headless chrome
	    $browser = $browserFactory->createBrowser(['noSandbox'=>true]);

	    // creates a new page and navigate to an url
	    $page = $browser->createPage();

	    $page->navigate($url)->waitForNavigation();
	
		$options = [
				'printBackground' => true,   // default to false
				//  'displayHeaderFooter' => true, // default to false
				'preferCSSPageSize' => true, // default to false ( reads parameters directly from @page )
				//'marginTop' => 0.0, // defaults to ~0.4 (must be float, value in inches)
				//  'marginBottom' => 1.4, // defaults to ~0.4 (must be float, value in inches)
				//  'marginLeft' => 0.4, // defaults to ~0.4 (must be float, value in inches)
				//  'marginRight' => 0.4, // defaults to ~0.4 (must be float, value in inches)
				'paperWidth' => $width, // defaults to 8.5 (must be float, value in inches)
				'paperHeight' => $height, // defaults to 8.5 (must be float, value in inches)
				// 'headerTemplate' => "<div>foo</div>", // see details bellow
				//'footerTemplate' => "<div>foo</div>", // see details bellow
				//'scale' => 1.2, // defaults to 1
			];
	
		// pdf
		$page->pdf($options)->saveToFile($pdf_file);
		$browser->close();
	
		return $pdf_file;
	}

	function fromHtml($html,$pdf_file='',$landscape=false)
	{
		$tmp_dir = public_path("tmp");
		$html_file = "$tmp_dir/" . Str::uuid() . ".html";
		$html_url  = url("/tmp/" . basename($html_file));

		if (!is_dir($tmp_dir))
			mkdir($tmp_dir);
		file_put_contents($html_file, $html);

		$pdf_file = pdfUtils::fromUrl($html_url);

		unlink($html_file);

		return $pdf_file;
	}
}
