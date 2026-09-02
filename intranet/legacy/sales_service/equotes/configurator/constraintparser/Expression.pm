# *****************************************************************************
# Author  : Bernd Loske
# Date    : June 16, 2002
# Comments:
#   This script is part of a utility to parse Baan constraints and
#	generate programs in other languages
#
# $Id: Expression.pm,v 2.0 2003/01/17 20:57:56 dpayton Exp $
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

package util::constraintparser::Expression;

use util::constraintparser::Symbol;

use Exporter;
use vars qw(@ISA @EXPORT);
@ISA = qw(Exporter);
@EXPORT = qw(OPERATOR_NONE OPERATOR_ADD OPERATOR_SUB OPERATOR_DIV OPERATOR_MULT
    	     OPERATOR_REMAINDER OPERATOR_CONCAT OPERATOR_NEG
	     OPERATOR_EQ OPERATOR_NE OPERATOR_GT OPERATOR_GE OPERATOR_LT 
	     OPERATOR_LE OPERATOR_OR OPERATOR_AND OPERATOR_NOT 
	     OPERATOR_BASICREF OPERATOR_ASSIGN);

use constant OPERATOR_NONE		=> 0x00;
use constant OPERATOR_ADD		=> 0x01;
use constant OPERATOR_SUB		=> 0x02;
use constant OPERATOR_DIV		=> 0x03;
use constant OPERATOR_MULT		=> 0x04;
use constant OPERATOR_REMAINDER		=> 0x05;
use constant OPERATOR_CONCAT		=> 0x06;
use constant OPERATOR_NEG		=> 0x07;

# NOTE: These must be grouped as some code checks to see if the operator
# is a conditional by using "... >= OPERATOR_EQ && ... <= OPERATOR_NOT"
use constant OPERATOR_EQ		=> 0x08;
use constant OPERATOR_NE		=> 0x09;
use constant OPERATOR_GT		=> 0x0A;
use constant OPERATOR_GE		=> 0x0B;
use constant OPERATOR_LT		=> 0x0C;
use constant OPERATOR_LE		=> 0x0D;

use constant OPERATOR_OR		=> 0x0E;
use constant OPERATOR_AND		=> 0x0F;
use constant OPERATOR_NOT		=> 0x10;

use constant OPERATOR_BASICREF		=> 0x11;
use constant OPERATOR_ASSIGN		=> 0x12;

sub new {
	my $this = shift;
	my $class = ref($this) || $this;
	my $self = { 
	    operator => 0, 
	    datatype => DATATYPE_NONE,
	};
	$self->{operand1} = shift;
	$self->{operator} = shift if @_;
	$self->{operand2} = shift;

	bless $self,$class;

	$self->assignDatatype;
	return $self;
}

sub operand1 {
	my $self = shift;
	$self->{operand1} = shift if @_;
	return $self->{operand1};
}

sub operand2 {
	my $self = shift;
	$self->{operand2} = shift if @_;
	return $self->{operand2};
}

sub operator {
	my $self = shift;
	$self->{operator} = shift if @_;
	return $self->{operator};
}

sub assignDatatype {
	my $self = shift;
	my $datatype;

	if ($self->{operator} >= OPERATOR_EQ && 
	     $self->{operator} <= OPERATOR_NOT) {
            $self->{datatype} = DATATYPE_LONG;
	    return;
	}

	$datatype = $self->{operand1}->datatype;
	if ($self->{operand2} && $self->{operand2}->datatype != $datatype) {
	    my $datatype2 = $self->{operand2}->datatype;
	    if ($datatype == DATATYPE_ERROR || $datatype2 == DATATYPE_ERROR ||
	        (($datatype == DATATYPE_LONG || $datatype == DATATYPE_DOUBLE) &&
	          $datatype2 == DATATYPE_STRING) ||
		($datatype == DATATYPE_STRING && ($datatype2 == DATATYPE_LONG ||
		 $datatype2 == DATATYPE_DOUBLE))) {
		$datatype = DATATYPE_ERROR;
	    }
	    else {
		$datatype = DATATYPE_DOUBLE if $datatype == DATATYPE_DOUBLE || 
		    $datatype2 == DATATYPE_DOUBLE;
	    }
	}
	$self->{datatype} = $datatype;
}

sub datatype { $_[0]->{datatype} }

sub dump {
	my $self = shift;
	my $out = shift;
	my $indend = shift || "";
	if( $self->{operator} == OPERATOR_BASICREF ) {
		$self->{operand1}->dump($out,$indend);
		return;
	}
	print { $out } $indend, "== EXPRESSION ==\n";
	print { $out } $indend, "Operator:  ", $self->getOperatorName, "\n";
	print { $out } $indend, "Datatype:  ", 
	    getDataTypeName($self->{datatype}), "\n";
	if( defined($self->{operand1}) ) {
	    print { $out } $indend, "Operand 1:\n";
	    $self->{operand1}->dump($out,$indend."   ");
	}
	if( defined($self->{operand2}) ) {
	    print { $out } $indend, "Operand 2:\n";
	    $self->{operand2}->dump($out,$indend."   ");
	}
}

sub getOperatorName {
	my $self = shift;
	if( $self->{operator} == OPERATOR_NONE ) {
	    return "NONE";
	}
	elsif( $self->{operator} == OPERATOR_ADD ) {
	    return "+";
	}
	elsif( $self->{operator} == OPERATOR_SUB ) {
	    return "-";
	}
	elsif( $self->{operator} == OPERATOR_NEG ) {
	    return "unary -";
	}
	elsif( $self->{operator} == OPERATOR_DIV ) {
	    return "/";
	}
	elsif( $self->{operator} == OPERATOR_MULT ) {
	    return "*";
	}
	elsif( $self->{operator} == OPERATOR_REMAINDER ) {
	    return "\\";
	}
	elsif( $self->{operator} == OPERATOR_CONCAT ) {
	    return "&";
	}
	elsif( $self->{operator} == OPERATOR_EQ ) {
	    return "==";
	}
	elsif( $self->{operator} == OPERATOR_NE ) {
	    return "!=";
	}
	elsif( $self->{operator} == OPERATOR_GT ) {
	    return ">";
	}
	elsif( $self->{operator} == OPERATOR_GE ) {
	    return ">=";
	}
	elsif( $self->{operator} == OPERATOR_LT ) {
	    return "<";
	}
	elsif( $self->{operator} == OPERATOR_LE ) {
	    return "<=";
	}
	elsif( $self->{operator} == OPERATOR_OR ) {
	    return "or";
	}
	elsif( $self->{operator} == OPERATOR_AND ) {
	    return "and";
	}
	elsif( $self->{operator} == OPERATOR_NOT ) {
	    return "not";
	}
	elsif( $self->{operator} == OPERATOR_ASSIGN ) {
	    return ":=";
	}

	return "UNKNOWN";
}

1;
