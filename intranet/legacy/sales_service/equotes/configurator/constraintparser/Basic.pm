# *****************************************************************************
# Author  : Bernd Loske & Kevin Brock
# Date    : June 16, 2002
# Comments:
#   This script is part of a utility to parse Baan constraints and
#	generate programs in other languages
#
# $Id: Basic.pm,v 2.0 2003/01/17 20:57:56 dpayton Exp $
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

package util::constraintparser::Basic;

use Exporter;
use vars qw(@ISA @EXPORT);
@ISA = qw(Exporter);

@EXPORT = qw(BASICTYPE_NONE BASICTYPE_VARREF
	     BASICTYPE_FUNCTION BASICTYPE_STATIC);

use util::constraintparser::Symbol;

use constant BASICTYPE_NONE		=> 0x00;
use constant BASICTYPE_VARREF		=> 0x01;
use constant BASICTYPE_FUNCTION		=> 0x02;
use constant BASICTYPE_STATIC		=> 0x03;

sub new {
	my $this = shift;
	my $type = shift;
	my $class = ref($this) || $this;
	my $self = {	
	    type => $type,
	    datatype => DATATYPE_NONE,
	};
	if( $self->{type} == BASICTYPE_VARREF ) {
	    $self->{symbol} = shift;
	    $self->{subscriptstart} = shift if @_;
	    $self->{subscriptend} = shift if @_;
	}
	elsif( $self->{type} == BASICTYPE_FUNCTION ) {
	    $self->{symbol} = shift;
	    $self->{params} = shift;
	}
	elsif( $self->{type} == BASICTYPE_STATIC ) {
	    $self->{datatype} = shift;
	    $self->{value} = shift;
	}

	bless $self,$class;
	return $self;
}

sub dump
{
	my $self = shift;
	my $out = shift;
	my $indend = shift || "";
	if( $self->{type} == BASICTYPE_VARREF ) {
	    $self->{symbol}->dump($out, $indend);
	    if ($self->{subscriptstart}) {
	        print {$out} $indend, "Subscript Start\n";
		$self->{subscriptstart}->dump($out, $indend."  ");
		if ($self->{subscriptend}) {
		    print {$out} $indend, "Subscript End\n";
		    $self->{subscriptend}->dump($out, $indend."  ");
		}
	    }
	    return;
	}

	print { $out } $indend, "== BASIC ==\n";
	print { $out } $indend, "Type:     ";

	if( $self->{type} == BASICTYPE_FUNCTION ) {
	    print {$out} "Function Call\n";
	    print { $out } $indend, "Function: ", $self->{symbol}->name, "\n";
	    if( $self->{params} && @{$self->{params}} ) {
		print { $out } $indend, "Params:\n";
		for ( @{$self->{params}} ) {
		    $_->dump($out,$indend."  ");
		}
	    }
	    else {
		print { $out } $indend, "Params:   NONE\n";
	    }
	}
	elsif( $self->{type} == BASICTYPE_STATIC ) {
	    print {$out} "Static Value\n";
	    print { $out } $indend, "Datatype: ";
	    if( $self->{datatype} == DATATYPE_NONE ) {
		print { $out } "NONE\n";
	    }
	    elsif( $self->{datatype} == DATATYPE_LONG ) {
		print { $out } "Long\n";
	    }
	    elsif( $self->{datatype} == DATATYPE_STRING ) {
		print { $out } "String\n";
	    }
	    elsif( $self->{datatype} == DATATYPE_DOUBLE ) {
		print { $out } "Double\n";
	    }
	    else {
		print { $out } "Unknown\n";
	    }
	    print {$out} $indend, "Value:    <", $self->{value}, ">\n";
	}
	else {
	    print {$out} "Unknown\n";
	}
}

sub type {
	my $self = shift;
	$self->{type} = shift if @_;
	return $self->{type};
}

sub datatype {
	my $self = shift;
	return $self->{symbol}{datatype} if $self->{symbol};
	return $self->{datatype};
}

1;
