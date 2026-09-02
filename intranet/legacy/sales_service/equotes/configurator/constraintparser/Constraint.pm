# *****************************************************************************
# Author  : Bernd Loske & Kevin Brock
# Date    : June 16, 2002
# Comments:
#   This script is part of a utility to parse Baan constraints and
#	generate programs in other languages
#
# $Id: Constraint.pm,v 2.0 2003/01/17 20:57:56 dpayton Exp $
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

package util::constraintparser::Constraint;

use util::constraintparser::Symbol;

sub new {
	my ($this, $symtable, $statements) = @_;
	my $class = ref($this) || $this;
	my $self = {
	    name => "",
	    type => "",
	    symtable => $symtable,
	    scope => $symtable->current,
	    statements => $statements,
	};

	bless $self,$class;
}

sub dump
{
	my $self = shift;
	my $out = shift || *STDERR;
	my $indend = shift || "";
	print { $out } $indend, "== CONSTRAINT ==\n";
	print { $out } $indend, "Name:      ", $self->{name}, "\n";
	print { $out } $indend, "Type:      ";
	if( $self->{type} == 1 ) {
	    print { $out } "before input\n";
	}
	elsif( $self->{type} == 2 ) {
	    print { $out } "validation\n";
	}
	elsif( $self->{type} == 3 ) {
	    print { $out } "parameter substitution\n";
	}
	else {
	    print { $out } "unknown\n";
	}
	print { $out } $indend, "Symbol Table:\n";
	$self->{symtable}->dump($out, $indend."   ");
	print { $out } $indend, "Statements:\n";
	for ( @{$self->{statements}} ) {
	    $_->dump($out,$indend."   ");
	}
}

sub name {
	my $self = shift;
	$self->{name} = shift if @_;
	return $self->{name};
}

sub type {
	my $self = shift;
	$self->{type} = shift if @_;
	return $self->{type};
}

sub symtable { $_[0]->{symtable} }
sub scope { $_[0]->{scope} }

1;
