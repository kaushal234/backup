# =============================================================================
# File    : Symbol.pm
# Author  : Kevin Brock
# Date    : July 2002
# Comments:
#    Symbol entry for constraint parser
#
# =============================================================================
# $Id: Symbol.pm,v 2.0 2003/01/17 20:57:56 dpayton Exp $
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

package util::constraintparser::Symbol;

use strict;

use Carp;

use Exporter;
use vars qw(@ISA @EXPORT);
@ISA = qw(Exporter);

@EXPORT = qw(DATATYPE_NONE DATATYPE_LONG DATATYPE_DOUBLE DATATYPE_STRING
	     DATATYPE_ERROR
	     SYM_VARIABLE SYM_FEATURE SYM_FUNCTION
	     SYM_GLOBAL SYM_REFERENCED SYM_ASSIGNED_TO SYM_SYSTEMDEFINED
	     getDataTypeName computeSymbolId makeOptionsMask);

use constant DATATYPE_NONE	=>	0x00;
use constant DATATYPE_LONG	=>	0x01;
use constant DATATYPE_DOUBLE	=>	0x02;
use constant DATATYPE_STRING	=>	0x03;
use constant DATATYPE_ERROR     =>	0x10;

# These are for a bitmap
use constant SYM_VARIABLE	=>	0x0001;
use constant SYM_FEATURE	=>	0x0002;
use constant SYM_FUNCTION	=>	0x0004;
use constant SYM_TYPE_MASK 	=>	0x000F;

use constant SYM_GLOBAL		=>	0x0010;
use constant SYM_ASSIGNED_TO	=>	0x0020;
use constant SYM_REFERENCED     =>	0x0040;
use constant SYM_SYSTEMDEFINED	=>	0x0080;

sub new {
    my ($this, $symtype, $name, $datatype, @options) = @_;
    my $class = ref($this) || $this;

    croak "symbol type required" unless $symtype;
    croak "symbol name required" unless $name;
    croak "data type required" unless $datatype;

    my $self = {
    	name	 => $name,
	datatype => $datatype,
	id 	 => computeSymbolId($symtype, $name),
	flags    => $symtype,
    };

    $self->{flags} |= $_ for (grep { defined $_ } @options);

    bless $self, $class;
}

sub datatype { $_[0]->{datatype} }
sub name { $_[0]->{name} }
sub id   { $_[0]->{id}   }
sub isFunction { ($_[0]->{flags} & SYM_FUNCTION) != 0 }
sub isFeature { ($_[0]->{flags} & SYM_FEATURE) != 0 }
sub isVariable { ($_[0]->{flags} & SYM_VARIABLE) != 0 }

sub isGlobal { ($_[0]->{flags} & SYM_GLOBAL) != 0 }
sub isAssignedTo { ($_[0]->{flags} & SYM_ASSIGNED_TO) != 0 }
sub isReferenced { ($_[0]->{flags} & SYM_REFERENCED) != 0 }
sub isSystemDefined { ($_[0]->{flags} & SYM_SYSTEMDEFINED) != 0 }

sub output_name {
    my $self = shift;
    $self->{outname} = $_[0] if @_;
    return defined $self->{outname} ? $self->{outname} : $self->{name};
}

sub symtypeName {
    my $self = shift;

    my $symtype = $self->{flags} & SYM_TYPE_MASK;
    return "variable" if ($symtype == SYM_VARIABLE);
    return "feature"  if ($symtype == SYM_FEATURE);
    return "function" if ($symtype == SYM_FUNCTION);
    return "unknown";
}

sub markAssigned {
    my $self = shift;
    $self->{flags} |= (SYM_ASSIGNED_TO | SYM_REFERENCED);
}

sub markReferenced {
    my $self = shift;
    $self->{flags} |= SYM_REFERENCED;
}

sub hasOptions {
    my ($self, $optmask) = @_;
    return 0 unless $optmask;
    return ($self->{flags} & $optmask) == $optmask;
}

sub dump {
    my $self = shift;
    my $out = shift;
    my $indend = shift || "";
    
    print {$out} $indend, "== SYMBOL ==\n";
    print {$out} $indend, "Name: ", $self->{name}, 
                 (defined $self->{outname} ? " => " . $self->{outname} : ""),
		 "\n";
    print {$out} $indend, "Type: ", $self->symtypeName, "\n";
    print {$out} $indend, "Data: ", getDataTypeName($self->{datatype}), "\n";
    print {$out} $indend, "Flag: ", 
	($self->isSystemDefined ? "system " : ""),
        ($self->isGlobal ? "global " : ""),
	($self->isReferenced ? "referenced " : ""),
	($self->isAssignedTo ? "assigned " : ""), "\n";
}

## non-method functions

sub computeSymbolId {
    my $symtype = shift || croak "symbol type required";
    my $name = shift || croak "symbol name required";
    chr($symtype + 48) . $name;
}

sub makeOptionsMask {
    my (@options) = @_;
    my $m = 0;
    $m |= $_ for (@options);
    return $m;
}

sub getDataTypeName {
    my $datatype = shift;
    return "Long" if ($datatype == DATATYPE_LONG);
    return "Double" if ($datatype == DATATYPE_DOUBLE);
    return "String" if ($datatype == DATATYPE_STRING);
    return "ERROR" if ($datatype == DATATYPE_ERROR);
    return "UNKNOWN";
}

1;
