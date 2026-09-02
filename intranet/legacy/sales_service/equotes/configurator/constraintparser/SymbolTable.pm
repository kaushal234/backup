# =============================================================================
# File    : SymbolTable.pm
# Author  : Kevin Brock
# Date    : July 2002
# Comments:
#    Symbol table for constraint parser
#
# =============================================================================
# $Id: SymbolTable.pm,v 2.0 2003/01/17 20:57:57 dpayton Exp $
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

package util::constraintparser::SymbolTable;

use strict;

use Carp;
use util::constraintparser::Symbol;

sub new {
    my $this = shift;
    my $class = ref($this) || $this;

    my $self = {
	nextid => 1,
        table => [ { 
	    id => 0,
	    depth => 0,
	    symbols => [], 
	    lookup => {}, 
	    parent => undef,
	} ],
    };
    $self->{current} = $self->{table}[0];
    bless $self, $class;
}

sub pushScope {
    my $self = shift;
    croak "symbol table closed" unless $self->{current};

    push @{$self->{table}}, { 
	id => $self->{nextid}++,
	depth => $self->{current}{depth} + 1,
        symbols => [], 
	lookup => {}, 
	parent => $self->{current} };
    $self->{current} = $self->{table}[-1];
    return $self->{current};
}

sub popScope {
    my $self = shift;
    croak "symbol table closed" unless $self->{current};
    croak "cannot pop global scope" unless $self->{current}{parent};

    # Note, this must be last to return the value of $self->{current};
    $self->{current} = $self->{current}{parent};
}

sub close {
    my $self = shift;
    croak "symbol table already closed" unless $self->{current};
    $self->{current} = undef;
}

sub current { $_[0]->{current} }

sub findSymbol {
    my $self = shift;
    my $type = shift || croak "fundSymbol must be given a type";
    my $name = shift || croak "findSymbol must be given a name";
    my $scope = shift || $self->{current} || 
                croak "no known current scope; scope must be supplied";

    my $id = computeSymbolId($type, $name);
    while ($scope) {
        return $scope->{lookup}{$id} if $scope->{lookup}{$id};
	$scope = $scope->{parent};
    }

    return undef;
}

sub add {
    my $self = shift;
    my $symbol = shift || croak "symbol reference required";
    my $scope = shift || $self->{current} ||
                croak "no known current scope; scope must be supplied";

    if ($scope->{lookup}{$symbol->id}) {
#        print STDERR "warning: attempt to define duplicate symbol: ",
#	    $symbol->symtypeName, ' ', $symbol->name, "\n";
	return 0;
    }

    push @{$scope->{symbols}}, $symbol;
    $scope->{lookup}{$symbol->id} = $scope->{symbols}[-1];
    return 1;
}

sub getSymbolList {
    my ($self, $scope, @options) = @_;
    $scope = $self->{table}[0] unless $scope; # The root scope
    croak "scope still open, list not available" if $self->_scopeIsOpen($scope);

    return @{$scope->{symbols}} unless @options;
    my @list;
    my $opts = makeOptionsMask(@options);
    for (@{$scope->{symbols}}) {
        push @list, $_ if $_->hasOptions($opts);
    }
    return @list;
}

sub dump {
    my $self = shift;
    my $out  = shift;
    my $indend = shift || "";

    print {$out} $indend, "== SYMBOL TABLE ==\n";
    $self->dumpScope($_, $out, $indend) for (@{$self->{table}});
}

sub dumpScope {
    my $self = shift;
    my $scope = shift;
    my $out = shift;
    my $indend = shift || "";

    $indend .= (" " x ($scope->{depth} * 3));
    print {$out} $indend, "== SCOPE ", $scope->{id}, " ==\n";
    print {$out} $indend, "Parent: ", 
        ($scope->{parent} ? $scope->{parent}->{id} : "(root)"), "\n";
    for (@{$scope->{symbols}}) {
	$_->dump($out, $indend."   ") 
	    if $scope->{id} > 0 || $_->isReferenced;
    }
}

## Internal methods
sub _scopeIsOpen {
    my $self = shift;
    my $scope = shift;

    for (my $sref = $self->{current}; $sref; $sref = $sref->{parent}) {
        return 1 if $sref == $scope;
    }

    return 0;
}

1;
