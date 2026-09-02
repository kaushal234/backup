# *****************************************************************************
# Author  : Bernd Loske
# Date    : June 16, 2002
# Comments:
#   This script is part of a utility to parse Baan constraints and
#	generate programs in other languages
#
# $Id: Condition.pm,v 2.0 2003/01/17 20:57:56 dpayton Exp $
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

use strict;

package util::constraintparser::Condition;

sub new {
	my $this = shift;
	my $class = ref($this) || $this;
	my $self = { };
	$self->{condition} = shift;
	$self->{truepart} = shift;
	$self->{falsepart} = shift;

	bless $self,$class;
	return $self;
}

sub dump
{
	my $self = shift;
	my $out = shift;
	my $indend = shift || "";
	print { $out } $indend, "== CONDITION ==\n";
	if( $self->{condition} ) {
	    print { $out } $indend, "Condition:\n";
	    $self->{condition}->dump($out,$indend."   ");
	}
	print { $out } $indend, "If true:\n";
	for ( @{$self->{truepart}} ) {
	    $_->dump($out,$indend."   ");
	}
	if( @{$self->{falsepart}} ) {
	    print { $out } $indend, "If false:\n";
	    for ( @{$self->{falsepart}} ) {
		    $_->dump($out,$indend."   ");
	    }
	}
}

1;
