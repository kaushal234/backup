# =============================================================================
# File    : Global.pm
# Author  : Kevin Brock
# Date    : July, 2002
# Comments:
#    Global configurator variables - used by constraints
#
# =============================================================================
# $Id: Globals.pm,v 2.0 2003/01/17 20:44:07 dpayton Exp $
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

package configurator::Globals;

use Carp;

sub new
{
    my $this = shift;
    my $class = ref($this) || $this;
    my $self = {};

    bless $self, $class;
}

sub set
{
    my ($self, $name, $value) = @_;
    croak "Variable name is required" unless $name;
    croak "Value is required" unless defined $value;
    $self->{$name} = $value;
}

sub getString
{
    my ($self, $name) = @_;
    croak "Variable name is required" unless $name;
    $self->{$name} = "" unless defined $self->{$name};
    return $self->{$name};
}

sub getNumber
{
    my ($self, $name) = @_;
    croak "Variable name is required" unless $name;
    $self->{$name} = 0 unless defined $self->{$name};
    return $self->{$name};
}

sub getDouble { goto &getNumber; }
sub getInteger { goto &getNumber; }

1;
