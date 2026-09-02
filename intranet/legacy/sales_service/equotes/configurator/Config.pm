# ***************************************************************************** # Author  : Kevin Brock
# Date    : July 2002
# Comments:
#   Configurator main form
#
# $Id: Config.pm,v 2.0 2003/01/17 20:44:07 dpayton Exp $
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

use strict;

package configurator::Config;

use Carp;
use CGI qw(:standard);

use WiseGlobal;
use WiseUtil;
use WiseWeb;

use Exporter;
use WiseForm;
use vars qw(@ISA @EXPORT_OK);
@ISA = qw(WiseForm Exporter);

@EXPORT_OK = qw(start_configurator);

sub new
{
	my ($this, $formname, $userstate, $restart) = @_;
        my ($config, %fields);

	# Retrieve main fields from the form
	$fields{"product"} = uc param("product") || "";

	# Change action into a button
	set_button(param("action")) if param("action");

	my $class = ref($this) || $this;
	$config = $class->SUPER::new($formname, $userstate, $restart);
	$config->{fields} = \%fields;

	return ($config);
}

sub print_before
{
	my $self = shift;
	$self->formmap("product", $self->{fields}{product});
	return 1;
}

sub button_exec
{
	my ($self, $button) = @_;

	return 1 if $self->SUPER::button_exec($button);

	if ($button eq "configure")
	  { 
	  start_configurator($self, $self->{fields}{product}, 0);
	  $self->print if $self->error;
	  }
	elsif ($button eq "configurepopup")
	  { 
	  start_configurator($self, $self->{fields}{product}, 1);
	  }
	elsif ($button eq "cancel")
	  {
	  $self->print;
	  }
	elsif ($button eq "setcaller")
	  {
	  $self->mess("pcfg_variantmade", param("variant") || "(unknown)");
	  # Variant %s successfully created
	  $self->print;
	  }
	elsif ($button eq "findprod")
	  # FIXME: This is not valid as configurator items come
	  # from a different query -- this shows all items, just
	  # like in Baan.
	  { $self->form_show("common/iteminq", "product") }
        else
	  { return 0 }

	return 1;
}

# Non-method functions; available to other programs

sub start_configurator
{
	my $form = shift;
	my $product = shift;
	my $popup = shift;
	$popup = 1 unless defined $popup;

	# Configurator must be enabled on the host
	my ($erp, $res);
        $erp = $form->erp_connect;
	unless (defined $form->{formdata}{__CONFIG}) 
	  {
	  $form->{formdata}{__CONFIG}{implemented} = 0;
	  $res = $erp->request("CONFIGURATOR_PARAMETERS", {});
	  if ($res && $res->[0]{configurator_implemented} == 1)
	    {
	    $form->{formdata}{__CONFIG}{implemented} = 1;
	    }
	  }
	unless ($form->{formdata}{__CONFIG}{implemented})
	  {
	  configerror_msg($form, $popup, "pcfg_notimplemented");
	  return;
	  }

	# Must have specified a main product
	unless ($product)
	  {
	  configerror_msg($form, $popup, "pcfg_prodrequired");
	  return;
	  }

	# Must be configurable (and exist too)
	$res = $erp->request("CAN_CONFIGURE_ITEM", { product => $product } );
	unless ($res && $res->[0]{product})
	  {
	  configerror_msg($form, $popup, "pcfg_notconfigurable", $product);
	  return;
	  }

	# Start configuration
	$form->formmap("curver", $global{path}[0]);
	$form->formmap("product", $product);
	$form->formmap("popup", $popup);
	send_tmplfile("configurator/config/startconfig.html", $form->formmap);
}

sub configerror_msg
{
	my ($form, $popup, $msg, @parms) = @_;

	$form->raise_error($msg, @parms);
	return unless $popup;
	send_tmplfile("configurator/config/configerror.html", $form->formmap);
}

1;
