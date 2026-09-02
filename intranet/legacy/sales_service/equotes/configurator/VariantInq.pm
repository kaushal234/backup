# *****************************************************************************
# Author  : Doug Payton
# Date    : 07/02/2002
# Comments:
#   Variant inquiry
#
# $Id: VariantInq.pm,v 2.0 2003/01/17 20:44:08 dpayton Exp $
#
# =============================================================================
# - Copyright (C) 2000-2002 Fullscope.  All Rights Reserved.                  -
# -     								      -
# - This software program contains proprietary technology and trade           -
# - secrets developed by Fullscope through substantial creative effort.       -
# - No part of this software may be duplicated, reused, or disclosed          -
# - without a duly authorized license agreement and/or written permission     -
# - from Fullscope.                                                           -
# =============================================================================
#
#
# *****************************************************************************
# Modifications
# *****************************************************************************

use strict;

package configurator::VariantInq;

use CGI qw(:standard);
use WiseGlobal;
use WiseWeb;
use WiseForm::Inq;
use vars qw(@ISA);
@ISA = qw(WiseForm::Inq);

sub new
{
	my ($this, $formname, $userstate, $restart) = @_;
	my ($inq, %fields);

	# Form selection fields -- always reset these
	$fields{variant} = uc param('variant') || "";
	$fields{product} = uc param('product') || "";
	$fields{description} = uc param('description') || "";

	# Setup and process the form; 
	my $class = ref($this) || $this;
	$inq = $class->SUPER::new($formname, $userstate, \%fields, $restart);
	$inq->msg("notfound", "variant_notfound");
	$inq->set_default_sort("variant", 1);

	# Lookup may be called from a line oriented form; 
	# check to see if such a button was used; 
	# if so, get the product from an associated product field.
	if (find_button() =~ /(\d+)$/)
	  {
	  my $line = $1;
	  $fields{product} = uc param("prod$line")
	    if defined param("prod$line");
	  $fields{product} = uc param("product$line")
	    if defined param("product$line");

	  if ($fields{product})
	    {
	    set_button("find");
	    $inq->initstate(0);
	    $inq->autosave(1);
	    }
	  }

	return ($inq);
}

sub print_after
{
	my ($self, $rowmap, $row) = @_;

	if ($row >= 0)
	  {
	  $rowmap->{variant_rep} = CGI::escape($rowmap->{variant});
	  }
}

1;
