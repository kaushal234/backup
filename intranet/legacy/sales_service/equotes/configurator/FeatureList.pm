# =============================================================================
# File    : FeatureList.pm
# Author  : Kevin Brock
# Date    : July, 2002
# Comments:
#    Tracks a list of features (Feature.pm) as an object
#
# =============================================================================
# $Id: FeatureList.pm,v 2.0 2003/01/17 20:44:07 dpayton Exp $
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

package configurator::FeatureList;

use Carp;

sub new
{
    my ($this) = @_;
    my $class = ref($this) || $this;
    my $self = {
      list	=> [],
      nameRef	=> {},
      };

    bless $self, $class;
}

sub clear
{
    my $self = shift;
    @{$self->{list}} = ();
    %{$self->{nameRef}} = ();
}

sub add
{
    my ($self, $name, $feature) = @_;
    croak "Feature name required" unless $name;
    croak "Feature obj referenece required" unless $feature &&
    	ref($feature) eq "configurator::Feature";
    unless ($self->{nameRef}{$name})
      {
      push @{$self->{list}}, undef;
      $self->{nameRef}{$name} = \$self->{list}[-1];
      }
    ${$self->{nameRef}{$name}} = $feature;
}

sub getList
{
    my $self = shift;
    return $self->{list};
}

sub get
{
    my ($self, $feature) = @_;
    croak "Feature name required" unless $feature;
    return ${$self->{nameRef}{$feature}} if defined $self->{nameRef}{$feature};
    return undef;
}

sub getString { shift->get(@_)->getString }
sub getInteger { shift->get(@_)->getInteger }
sub getDouble { shift->get(@_)->getDouble }

sub set
{
    my ($self, $feature, $value) = @_;
    croak "Feature name required" unless $feature;
    croak "Feature not in list" unless $self->{nameRef}{$feature};
    ${$self->{nameRef}{$feature}}->set($value);
}

1;
