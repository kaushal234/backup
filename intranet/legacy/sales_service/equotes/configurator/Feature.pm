# =============================================================================
# File    : Feature.pm
# Author  : Kevin Brock
# Date    : July, 2002
# Comments:
#    Single feature for the product configurator
#
# =============================================================================
# $Id: Feature.pm,v 2.0 2003/01/17 20:44:07 dpayton Exp $
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

package configurator::Feature;

use Carp;

sub new
{
    my ($this, $name, $desc, $type, $seq) = @_;
    my $class = ref($this) || $this;
    my $self = {
      name 	=> $name,
      value	=> undef,
      desc	=> $desc,
      type	=> $type,
      optlist	=> undef,
      seq	=> $seq,
      };

    bless $self, $class;
}

sub set_constraint
{
    my ($self, $name, $constclass) = @_;
    croak "No constraint class" unless $constclass;
    $self->{constraint} = $constclass->getConstraint(
        configurator::Processor::getObjectName($name));
}

sub add_option
{
    my $self = shift;
    my ($value, $name) = @_;
    croak "Option name required" unless defined $name;
    croak "Option value required" unless defined $value;

    $self->{optlist} = [] unless $self->{optlist};
    push @{$self->{optlist}}, { name => $name, value => $value };
    $self->{value} = $value unless defined $self->{value};
}

sub find_option
{
    my $self = shift;
    croak "No option list defined" unless $self->{optlist};
    my $value = shift;

    if ($self->{type} eq "string")
      {
      for my $opt (@{$self->{optlist}})
	{
	return $opt if $opt->{value} eq $value;
	}
      }
    else
      {
      for my $opt (@{$self->{optlist}})
	{
	return $opt if $opt->{value} == $value;
	}
      }

    return undef;
}

sub value
{
    my $self = shift;
    $self->{value} = $_[0] if @_;
    $self->resetValue unless defined $self->{value};
    return $self->{value};
}

sub resetValue
{
    my $self = shift;
    if ($self->{optlist})
      {
      $self->{value} = $self->{optlist}[0]{value};
      }
    else
      {
      $self->{value} = ($self->{type} eq "string" ? "" : "0");
      }
}

sub get { $_[0]->{value}; }
sub getString { goto &get }
sub getInteger { goto &get }
sub getDouble { goto &get }
sub set { $_[0]->{value} = $_[1]; }

1;
