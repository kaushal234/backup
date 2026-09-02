#!/usr/bin/perl -w
use HTML::HTMLDoc;
use DBI;
use Tie::IxHash;
use Imager;
use Archive::Zip;
use CGI;

use Image::Magick;

$LOCAL_PATH_TO_ROOT="/home/www/www.tld-gse.com";
$ONLINE_PATH_TO_ROOT="https://www.tld-gse.com";
#$DB_SOURCE =	"dbi:mysql:database=tld;host=127.0.0.1";
#$DB_USER =	"www";
#$DB_PW =	"tired";
#$DB_OPTS = "";
#$DB_OPTS = {'RaiseError' => 1};
$dbh = DBI->connect(	"dbi:mysql:database=tld;host=127.0.0.1",
						"www","tired",{'RaiseError' => "1"});
###############################################################################
#   Function switch
###############################################################################
$urlParams=new CGI;
$params = $urlParams->Vars();

my $m = $params->{'m'};
my $id = $params->{'id'};
my $lang = $params->{'lang'};
if(!defined($lang)){
	$lang = "en";
}
#$m="partsbookhtml";
#$id="1473";
#dlZippedManual(1134);
#print getHTMLPartsDiagram(1473,"test");
#dlPartsbookHTML(1473);

if($m eq "zipped"){
	dlZippedManual($id,$lang);
}elsif($m eq "partsbookpdf"){
	dlPartsbookPDF($id,$lang);
}elsif($m eq "partsdiagrampdf"){
	dlPartsdiagramPDF($id,$lang);
}elsif($m eq "partsbookhtml"){
	dlPartsbookHTML($id,$lang);
}

$dbh->disconnect();
exit;

###############################################################################
#   Start of sub routines
###############################################################################
sub dlPartsdiagramPDF{
	my $diagramID = shift;
	my $lang = shift;
	print "Content-Type: application/pdf\nContent-Disposition: attachment;filename=diagram_$diagramID.pdf\n\n";
	print getPDFPartsDiagram($diagramID,$lang);
}
sub dlPartsbookHTML{
	my $manualID = shift;
	my $lang = shift;
	print "Content-Type: text/html\n\n";
	my $HTMLPartsbook = getHTMLPartsbook($manualID,getManualHeader($manualID),$lang);
	$HTMLPartsbook =~ s/$LOCAL_PATH_TO_ROOT/$ONLINE_PATH_TO_ROOT/g;
	print $HTMLPartsbook;
}
sub dlPartsbookPDF{
	my $manualID = shift;
	my $lang = shift;
	print "Content-Type: application/pdf\nContent-Disposition: attachment;filename=partsbook_$manualID.pdf\n\n";
	print getPDFPartsbook($manualID,getManualHeader($manualID),$lang);
}

sub dlZippedManual{
	my ($manualID,$lang) = @_;

	my $zip = Archive::Zip->new();
	
	# Add the partsbook to zip file
	my $partsbookPDFString = getPDFPartsbook($manualID,"&nbsp;",$lang);
	if(defined($partsbookPDFString)){
		$zip->addString($partsbookPDFString,"Chapter-4_$manualID.pdf");
	}
	############################################################################
	#    Gather manual sections together for zipping
	############################################################################
	my $path =	"/home/www/www.tld-gse.com/en/private/uploads/manuals_diagrams";
	
	my $query =	"SELECT diagram_filename".
				" FROM manuals_docs,manuals_diag".
				" WHERE manuals_docs.parent_id=$manualID AND".
				" manuals_docs.doc_num=manuals_diag.id".
				" AND manuals_diag.doc_type like 'MANUAL%'".
				" ORDER BY manuals_docs.item";
	my $sth = $dbh->prepare($query)|| die $dbh->errstr;

	$sth->execute();
	if($sth->rows() > 0){
		while ($row = $sth->fetchrow_hashref()) {
			$filename = $row->{"diagram_filename"};
			($temp,$original) = split(/\-/,$filename,2);
#			$zip->addFile("$path/$filename",substr($filename,11));
			$zip->addFile("$path/$filename",$original);
		}
	}

	print "Content-Type: application/zip\nContent-Disposition: attachment;filename=tld_manual_$manualID.zip\n\n";
	$zip->writeToFileHandle(STDOUT);
}

sub getManualHeader{
	my ($manualID) = @_;
	###############################################################################
	#    Get manual header details
	###############################################################################
	my $query=	"select * from manuals where id='$manualID'";
	my $sth = $dbh->prepare($query)|| die $dbh->errstr."\n".$query."\n";
	$sth->execute();
	if($sth->rows() != 1){
		print "Can't find  manual $manualID\n";
		return;
	}
	my $manualDetails = $sth->fetchrow_hashref();
	############################################################################
	#      Start of document level header/footer generation
	############################################################################
	return <<"EOF";
	<font size="24"><b><img align="right" src="$LOCAL_PATH_TO_ROOT/shared/icons/tld-icon.gif">
	$manualDetails->{model}</b></font>
EOF
}

sub getUnitHeader{
	my ($unitSN) = @_;
	###############################################################################
	#    Get manual header details
	###############################################################################
	my $query=	"select * from service where id='$unitSN'";
	my $sth = $dbh->prepare($query)|| die $dbh->errstr."\n".$query."\n";
	$sth->execute();
	if($sth->rows() != 1){
		print "Can't find unit sn $unitSN\n";
		return;
	}
	my $unitDetails = $sth->fetchrow_hashref();
	############################################################################
	#      Start of document level header/footer generation
	############################################################################
	return <<"EOF";
	<font size="24"><b><img align="right" src="$LOCAL_PATH_TO_ROOT/shared/icons/tld-icon.gif">
	$unitDetails->{serial}</b></font>
EOF
}

sub getPDFPartsbook{
	my ($manualID,$pageHeader,$lang) = @_;
	############################################################################
	#   Start of PDF generation
	############################################################################	
#	my $htmldoc = new HTML::HTMLDoc('mode'=>'file', 'tmpdir'=>'/tmp'); 
	my $htmldoc = new HTML::HTMLDoc(); 
	$htmldoc->set_html_content(getHTMLPartsbook($manualID,$pageHeader,$lang));
#	$htmldoc->set_html_content(getHTMLPartsbook($manualID,"test"));
#	$htmldoc->set_compression("9");
	my $pdf = $htmldoc->generate_pdf();
#	$pdf->to_file('/tmp/mypdf.pdf');
	return $pdf->to_string();
}

###############################################################################
#  Returns HTML partsbook
###############################################################################
sub getHTMLPartsbook{
	my ($manualID,$pageHeader,$lang) = @_;
	###############################################################################
	#     Start of MAIN level header/footer generation
	###############################################################################
	my $mainHeader = <<"EOF";
	<html>
	<head>
	<title>Chapter 4 - Illustrated Parts List</title>
	<link rel="stylesheet" href="/tld-gse.css">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>
	<body>
	<h1>Chapter 4 - Illustrated Parts List</h1>
	$pageHeader
	<h2>Table of contents</h2>
EOF
	$mainHeader .=	getHTMLTOC($manualID,$lang).
					"<!-- NEW PAGE -->";

	$mainFooter = "</body></html>";
	###########################################################################
	#     Get manual document ids
	###########################################################################
	$query=	"select manuals_docs.doc_num from manuals_docs,manuals_diag".
			" where manuals_docs.parent_id=$manualID AND".
			" manuals_docs.doc_num=manuals_diag.id".
			" AND manuals_diag.doc_type='PARTS DIAGRAM'".
			" order by category,item";
	my $sth = $dbh->prepare($query)|| die $dbh->errstr;
	$sth->execute or die $sth->errstr;

	############################################################################
	#     Put pages together
	############################################################################
	if($sth->rows() > 0){
		while (@row = $sth->fetchrow_array()) {
			$documents .= 	getHTMLPartsDiagram($row[0],$pageHeader,$lang);
#print @row;
		}
	}else{
		$documents .= "No documents found for that manual ID\n";
	}
	return 	$mainHeader.
			$documents.
			$mainFooter;
}

sub getPDFPartsDiagram{
	my ($diagramID,$lang) = @_;
#	print "In getPartsDiagramPDF, diagramid=$diagramID\n";

#	my $htmldoc = new HTML::HTMLDoc('mode'=>'file', 'tmpdir'=>'/tmp'); 
	my $htmldoc = new HTML::HTMLDoc(); 
	############################################################################
	#      Start of document level header/footer generation
	############################################################################
	my $pageHeader = <<"EOF";
	<font size="24"><b><img align="right" src="$LOCAL_PATH_TO_ROOT/shared/icons/tld-icon.gif">
	Parts Diagram - $diagramID</b></font>
EOF
	my $body=	"<html><head><title>Chapter 4 - Illustrated Parts List - Parts Diagram# $diagramID</title></head><body>".
				getHTMLPartsDiagram($diagramID,$pageHeader,$lang).
				"</body></html>";
	$htmldoc->set_html_content($body);
#	$htmldoc->set_page_size("letter");
	my $pdf = $htmldoc->generate_pdf();

#print $pdf->to_string();
	return $pdf->to_string();
}

###############################################################################
#  Returns HTML partsdiagram
###############################################################################
sub getHTMLPartsDiagram{
	my ($diagramID,$pageHeader,$lang) = @_;
	my $query="select * from manuals_diag where id=$diagramID";
	my $sth = $dbh->prepare($query)|| die $dbh->errstr;
	$sth->execute();
	if($sth->rows() > 0){
		$diagramDetails = $sth->fetchrow_hashref();
		############################################################################
		#     Create parts diagram level header
		############################################################################
		my $partsDiagramHeader = <<"EOF";
		<h4><a name="$diagramDetails->{factory_num}"></a>
		$diagramDetails->{factory_num} - $diagramDetails->{$lang."description"}</h4>
EOF
		############################################################################
		#     Adjust diagram size and rotation
		############################################################################
		
		$imgFile=$diagramDetails->{diagram_filename};
		my $result = 	$pageHeader.
						$partsDiagramHeader;
		my $ext = lc(substr($imgFile,-4));
		if(defined($imgFile) &&	($ext eq ".jpg"|| $ext eq ".gif")){
			$result .= getHTMLDiagramBody($imgFile);
		}else{
			$result .= "<b>No diagram available</b>";
		}
#		$result .= 
		$result .=	"<!-- NEW PAGE -->\n".
#					$pageHeader.
					$partsDiagramHeader.
					getHTMLPartsDiagramTable($diagramID,$lang).
					"<a href=\"toc\">Back to Table of Contents</a><!-- NEW PAGE -->\n";
		return $result;
	}
}

###############################################################################
#  Returns HTML parts diagram diagram body
###############################################################################
sub getHTMLDiagramBody{
	my ($imgFile,$lang) = @_;
	if(!defined($imgFile)){
		return;
	}
	my $imgPath="/home/www/www.tld-gse.com/en/private/uploads/manuals_diagrams";
	my $imgFilePath="$imgPath/$imgFile";
#	my $img = Imager->new;
#		$img->read(file=>$imgFilePath)
#		    or die "Cannot find $imgFilePath : ". $img->errstr;

	my $img = new Image::Magick;
	$img->Read(filename => $imgFilePath);
	
	$maxImgW=700;
	$maxImgH=860;
	    
	my ($imgW,$imgH) = $img->Get('width','height');
#print $imgW, $imgH;
	#  Change to portrait
	if($imgW > $imgH){
		my $p_File = "p_$imgFile";
		$img->Rotate(degrees => 270);
		$img->Write(filename => "$imgPath/$p_File");
		($imgW,$imgH) = $img->Get('width','height');
		$imgFile = $p_File;
	}
	my $scaleFactor;
	if($imgW/$maxImgW > $imgH/$maxImgH){
		$scaleFactor=$maxImgW/$imgW;
	}else{
		$scaleFactor=$maxImgH/$imgH;
	}
	$imgW = $imgW * $scaleFactor;
	$imgH = $imgH * $scaleFactor;
	my $htmlSize="width=$imgW height=$imgH";
	my $result = <<"EOF";
	<p><img $htmlSize src="$imgPath/$imgFile"></p>
EOF
	return $result;
}

###############################################################################
#  Returns HTML parts diagram parts table
###############################################################################
sub getHTMLPartsDiagramTable{
	my $LINES_PER_PAGE = 40;
	my ($diagramID,$lang) = @_;
	tie(%fieldHash, 'Tie::IxHash',
				"item"=>"Item",
				"pn"=>"PN",
				"qty"=>"Qty",
				$lang=>"Description",
				"note"=>"Note",
				"group_a"=>"A",
				"group_b"=>"B",
				"group_c"=>"C"
			);
		
	my $query="select * from manuals_parts where parent_id='$diagramID' order by item";
	my $sth = $dbh->prepare($query)|| die $dbh->errstr;
	my $result="";
	$sth->execute();
	if($sth->rows() > 0){
		$result="<table width=100%>";
	#	Do table titles
		$result .= "<tr>";
		foreach $field(keys(%fieldHash)){
			$result .= "<td><font size='-1'>".$fieldHash{$field}."</font></td>";
		}
		$result .= "</tr>\n";
		my $lineNum = 1;
		my $pageNum = 0;
		while ($row = $sth->fetchrow_hashref()) {
			if($lineNum % $LINES_PER_PAGE == 0){
				$pageNum++;
				$result .= "</table>".
							"<!-- NEW PAGE -->".
							"<table width=100%>";
			}
			$result .= "<tr>";
			foreach $field(keys(%fieldHash)){
				$result .= "<td><font size='-1'>";
#				if($row->{$field} eq ''){
				if(!defined($row->{$field})){
					$result .= "&nbsp;";
				}else{
					$result .= $row->{$field};
				}
				$result .= "</font></td>";
			}
			$result .= "</tr>\n";
			$lineNum++;
		}
		$result .= "</table>\n";
		if($pageNum % 2 != 0){
			$result .= "<!-- NEW PAGE -->".
						"<h1>This page intentionally left blank</h1>";
		}
	}
	return $result;
}

###############################################################################
#  Returns HTML partsbook table of contents
###############################################################################
sub getHTMLTOC{
	my ($manualID,$lang) = @_;
	if($manualID eq ""){
		return "ERROR: No manual ID given";
	}
	$query=	"select distinct manuals_diag.category from manuals_diag,manuals_docs".
			" where manuals_docs.parent_id='$manualID' AND manuals_docs.doc_num=manuals_diag.id".
			" AND manuals_diag.doc_type='PARTS DIAGRAM'".
			" ORDER BY manuals_diag.category ASC";
	my $sth = $dbh->prepare($query)|| die $dbh->errstr;
	$sth->execute();
	if($sth->rows() > 0){
		while (@row = $sth->fetchrow_array()) {
			push(@categories,$row[0]);
		}
	}

	my $result .= "<ul>";
	my $lines = 0;
	my $pageNum = 0;
	############################
	my $MAX_LINES_PER_PAGE = 24;
	############################
	foreach $category(@categories){
		my $query=	"select manuals_diag.* from manuals_diag,manuals_docs".
				" where manuals_docs.parent_id='$manualID' AND manuals_docs.doc_num=manuals_diag.id".
				" AND manuals_diag.category='$category'".
				" AND manuals_diag.doc_type='PARTS DIAGRAM'".
				" order by item";
		$sth = $dbh->prepare($query)|| die $dbh->errstr;
		$sth->execute();
		$result .= "<a name=\"toc\"></a><li><b>$category</b>";
		if($sth->rows() > 0){
			$result .= "<ul>";
			while ($row = $sth->fetchrow_hashref()) {
				$result .= "<li><a href=\"#".$row->{factory_num}."\">".
						$row->{factory_num}.", ".$row->{$lang."description"}.
						"</a></li>";
				$lines++;
				if($lines % $MAX_LINES_PER_PAGE == 0){
					$pageNum++;
					$result .= "</ul></li><!-- NEW PAGE --><a name=\"toc\"></a><li><b>$category</b><ul>";
				}
			}
			$result.="</ul>";
		}
		$result .="</li>"
	}
	$result .= "</ul>";
	if($pageNum % 2 != 0){
		$result .= "<!-- NEW PAGE -->".
					"<h1>This page intentionally left blank</h1>";
	}

	return $result;
}