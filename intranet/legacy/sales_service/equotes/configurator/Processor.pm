# *****************************************************************************
# Author  : Kevin Brock
# Date    : July 2002
# Comments:
#   Configurator Processor -- handles request from Java configurator
#
# $Id: Processor.pm,v 2.1 2003/05/01 05:53:33 kbrock Exp $
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

package configurator::Processor;

use CGI qw(:standard Vars);
use Experimental::Exception;
##use Text::Wrap;

use WiseGlobal;
use WiseUtil;
use WiseWeb;

use configurator::Globals;
use configurator::FeatureList;
use configurator::Feature;

use WiseForm;
use vars qw(@ISA);
@ISA = qw(WiseForm);

sub new
{
	my ($this, $formname, $userstate, $restart) = @_;
        my ($obj);

	my $class = ref($this) || $this;
	$obj = $class->SUPER::new($formname, $userstate, $restart);
	return ($obj);
}

# configurator::Processor is special as it does not actually produce
# real HTML forms for WiseB2B.  Rather this emits XML directly for the
# Java configurator.  This is easiest to do by overridding the main
# execute method and providing our own custom code here.
sub execute
{
	my $self = shift;
	my $action = lc param('action') || "";
	my $autosave = 1;

	try {
	  if ($action eq 'init')
	    { 
	    $self->init;
	    $self->send_next_item unless $self->error;
	    }
	  elsif ($action eq 'next')
	    {
	    $self->process;
	    $self->send_next_item unless $self->error;
	    }
	  elsif ($action eq 'prev')
	    {
	    $self->process;
	    $self->send_prev_item unless $self->error;
	    }
	  elsif ($action eq 'finish')
	    {
	    $self->process;
	    unless ($self->error)
	      {
	      $self->finish;
	      }
	    }
	  elsif ($action eq 'setcaller')
	    {
	    $self->set_caller;
	    $autosave = 0;
	    $self->remove_state;
	    }
	  elsif ($action eq 'cancel' || $action eq 'close')
	    {
	    $self->cancel;
	    $autosave = 0;
	    $self->remove_state;
	    }
	  elsif ($action eq 'resetall')
	    {
	    $self->reset_all;
	    $self->send_next_item unless $self->error;
	    }
	  elsif ($action)
	    {
	    $self->raise_error("pcfg_badaction", $action);
	    # Invalid action command (%s) given in URL
	    }
	  else
	    { 
	    $self->raise_error("pcfg_noaction");
	    # URL request missing 'action'
	    }

	  $self->send_message if $self->error;
	  $self->save_state if $autosave;
	  }
	catch
	  Die => sub {
	    my $msg;
	    $msg = $_[1] || "Aborted, no message";
	    $self->send_fatal("pcfg_fatal", $msg);
	    wiselog(1, "Configurator Processor died: $msg");
	    },

	  Default => sub {
	    my $name = ref($_[0]) || $_[0];
	    $self->send_fatal("pcfg_fatal", $name);
	    wiselog(1, "Configurator Processor Exception: $name");
	    };
}

sub init
{
	my $self = shift;
	my ($erp, $req, $product);

	$erp = $self->erp_connect(escape => 0);
	$req = $erp->request("CONFIGURATOR_PARAMETERS", {});
	$self->erp_error, return unless $req;
	unless ($req->[0]{configurator_implemented} == 1)
	  {
	  $self->raise_error("pcfg_notimplemented");
	  # Configurator is not implemented on the host
	  return;
	  }
	my $multilevel = ($req->[0]{multilevel} == 1);

	$product = uc param('product') || "";
	unless ($product)
	  {
	  $self->raise_error("pcfg_noproduct");
	  # URL init request missing 'product' parameter 
	  return;
	  }

	$req = $erp->request("PRODCHECK", { product => $product });
	$self->erp_error, return unless $req;

	unless ($req->[0]{prod_name})
	  {
	  $self->raise_error("pcfg_notfound", $product);
	  # Product %s not found
	  return;
	  }

	# Initializes the state
	$self->{formdata} = {
	  product	=> $product,
	  desc		=> $req->[0]{prod_name},
	  variant	=> undef,
	  variant_desc	=> $req->[0]{prod_name},
	  effdate	=> get_today(),
	  globals	=> new configurator::Globals(),
	  multilevel	=> $multilevel,
	  pos		=> -1,
	  configuration => [ { 
	    product 	=> $product,
	    desc    	=> $req->[0]{prod_name},
	    features 	=> undef,
	    constclass 	=> undef,
	    parent	=> undef,
	    level	=> 1,
	    pos		=> 0,
	    seq		=> 0,
	    valid	=> 0,
	    }, ],
	  };
}

sub send_next_item
{
	my $self = shift;
	unless ($self->_send_next_item)
	  {
	  unless ($self->_send_prev_item(1))
	    {
	    $self->raise_error("pcfg_nofeatures", $self->{formdata}{product});
	    # No changeable features for %s
	    return;
	    }
	  }
}

sub send_prev_item
{
	my $self = shift;
	unless ($self->_send_prev_item)
	  {
	  unless ($self->_send_next_item)
	    {
	    $self->raise_error("pcfg_nofeatures", $self->{formdata}{product});
	    # No changeable features for %s
	    return;
	    }
	  }
}

sub _send_next_item
{
	my $self = shift;

	while ($self->get_next_item)
	  {
	  if ($self->has_unhidden_feature)
	    {
	    $self->send_features;
	    return 1;
	    }
	  else
	    {
	    $self->auto_process_display_only_set;
	    }
	  }

	return 0;
}

sub _send_prev_item
{
	my $self = shift;
	my $aslast = shift;

	while ($self->get_prev_item)
	  {
	  if ($self->has_unhidden_feature)
	    {
	    $self->send_features($aslast);
	    return 1;
	    }
	  }

	return 0;
}

sub get_next_item
{
	my $self = shift;
	my ($erp, $req, $f, $optlist);

	return if ($self->{formdata}{pos} >= 
	           $#{$self->{formdata}{configuration}});
	$self->{formdata}{pos}++;
	my $cref = $self->{formdata}{configuration}[$self->{formdata}{pos}];

	$cref->{features} = new configurator::FeatureList() 
	    unless $cref->{valid};
	$self->load_constraint_package($cref);
	return $cref if ($cref->{valid});

	$erp = $self->erp_connect(escape => 0);
	unless (defined $cref->{desc})
	  {
	  $req = $erp->request("PRODCHECK", { product => $cref->{product} });
	  $self->erp_error, return unless $req;
	  $cref->{desc} = $req->[0]{prod_name} || "(product missing)";
	  }

	if ($self->{formdata}{multilevel})
	  {
	  $req = $erp->request("ITEM_BOM", { product => $cref->{product},
			       effective_date => $self->{formdata}{effdate} });
	  $self->erp_error, return unless $req;

	  # Add the generic items into the configuration list
	  for (@{$req})
	    {
	    if ($_->{item_type} == 3)  # Generic Item
	      {
	      push @{$self->{formdata}{configuration}}, {
		  product	=> $_->{item},
		  valid		=> 0,
		  parent	=> $cref,
		  level		=> $cref->{level} + 1,
		  pos		=> $_->{position},
		  seq		=> $_->{sequence},
		  };

	      # Ensure there is no infinite loop in the BOM
	      if ($self->parent_matches($cref, $_->{item}))
		{
		$self->raise_error("pcfg_bom_loop", $_->{item});
		# Infinite loop in BOM detected with item %s
		return;
		}
	      }
	    }
	  }

	# Retrieve the feature list to use for the current item
	$req = $erp->request("ITEM_FEATURES", { product => $cref->{product},
			     effective_date => $self->{formdata}{effdate} });
	$self->erp_error, return unless $req;
	my ($feats, $feat);
	$feats = $cref->{features};

	$feats->clear;
	for $f (@{$req})
	  {
	  $feat = new configurator::Feature($f->{feature}, $f->{feature_desc},
	    qw(string integer double)[$f->{option_value_type} - 1],
	    $f->{sequence} );

	  if ($f->{constraint})
	    {
	    die "Main constraint class not loaded for $cref->{product} ",
		"(constraint $f->{constraint})"
		unless $cref->{constclass};
	    $feat->set_constraint($f->{constraint}, $cref->{constclass})
	    }

	  if ($f->{select_options} == 1)
	    {
	    $self->get_option_list($feat, $cref->{product}, $f->{sequence});
	    return if $self->error;
	    }

	  $feats->add($f->{feature}, $feat);
	  }
	
	$cref->{valid} = 1;

	return $cref;
}

sub get_prev_item
{
	my $self = shift;

	return if $self->{formdata}{pos} == 0;
	$self->{formdata}{pos}--;
	my $cref = $self->{formdata}{configuration}[$self->{formdata}{pos}];
	$self->load_constraint_package($cref);
	return $cref;
}

sub get_option_list
{
	my ($self, $feature, $product, $sequence) = @_;
	my ($erp, $req);

	$erp = $self->erp_connect(escape => 0);
	$req = $erp->request("FEATURE_OPTIONS", { 
			product => $product,
			sequence => $sequence,
			effective_date => $self->{formdata}{effdate} });
	$self->erp_error, return unless $req;

	$feature->add_option($_->{option}, $_->{option_desc}) for (@{$req});
}

sub has_unhidden_feature
{
	my $self = shift;
	my ($feat, $cref);

	my $globalvar = $self->{formdata}{globals};

	# Scan through the features - if there are any that
	# allow input then the user should see this set; otherwise just
	# move on to the next set.
	$cref = $self->{formdata}{configuration}[$self->{formdata}{pos}];
	for $feat (@{$cref->{features}->getList})
	  {
	  if ($feat->{constraint})
	    {
	    $globalvar->set("input", 1);
	    $globalvar->set("display", 1);
	    $feat->{constraint}->before_input;
	    return 1 if ($globalvar->getInteger("display") || 
	                 $globalvar->getInteger("input"));
	    }
	  else
	    {
	    return 1;
	    }
	  }
}

sub auto_process_display_only_set
{
	my $self = shift;
	my ($feat, $cref);

	$cref = $self->{formdata}{configuration}[$self->{formdata}{pos}];
	for $feat (@{$cref->{features}->getList})
	  {
	  $feat->{constraint}->parameter_substitution if $feat->{constraint};
	  }
}

sub parent_matches
{
	my ($self, $cref, $item) = @_;
	while ($cref)
	  {
	  return 1 if ($cref->{product} eq $item);
	  $cref = $cref->{parent};
	  }
	return 0;
}

sub reset_all
{
	my $self = shift;
	$self->reset_option_set($_) for (@{$self->{formdata}{configuration}});
	$self->{formdata}{pos} = -1;
}

sub reset_option_set
{
	my $self = shift;
	my $cref = shift;

	return unless $cref->{features};
        $_->resetValue for (@{$cref->{features}->getList});
}

sub process
{
	my $self = shift;
	my ($feature, $valid, $value);

	my $globalvar = $self->{formdata}{globals};

	my $cref = $self->{formdata}{configuration}[$self->{formdata}{pos}];
	$self->load_constraint_package($cref);

	$self->{formdata}{variant_desc} = param("variant_desc")
	    if param("variant_desc");

	# Retrieve the passed features and options
	for $feature (@{$cref->{features}->getList})
	  {
	  $value = param("f_" . $feature->{name});
	  unless (defined $value)
	    {
	    $self->raise_error("pcfg_featmsng", $feature->{name});
	    # Feature %s missing from returned result
	    return;
	    }

	  # Verify the data type
	  if ($feature->{optlist})
	    {
	    # FIXME: For now this is commented out as with some test data
	    # (valid in Baan) the options could be set to values not in
	    # the option list by other constraints.
	    #unless (defined $feature->find_option($value))
	    #  {
	    #  $self->raise_error("pcfg_badoption", $feature->{name}, $value);
	    #  # Invalid option value for feature %s (%s)
	    #  return;
	    #  }
	    }
	  else
	    {
	    if ($feature->{type} eq "long")
	      {
	      $valid = ($value =~ m/^-?\d+$/);
	      }
	    elsif ($feature->{type} eq "double")
	      {
	      $valid = ($value =~ m/^-?\d+(?:\.\d+)?$/);
	      }
	    else
	      {
	      $valid = 1;
	      }

	    unless ($valid)
	      {
	      $self->raise_error("pcfg_badvalue", $feature->{name}, $value);
	      # Invalid value for feature %s (%s)
	      return;
	      }
	    }

	  $feature->set($value);
	  }

	# Revalidate all features with constraints
	for $feature (@{$cref->{features}->getList})
	  {
	  if ($feature->{constraint})
	    {
	    $globalvar->set("validate", 1);
	    $feature->{constraint}->validation;
	    unless ($globalvar->getInteger("validate"))
	      {
	      $self->raise_error("pcfg_failedconst", 
	          $feature->{constraint}{origname}, $feature->{name}, $value);
	      # Constraint %s for feature %s failed (%s)
	      return;
	      }
	    }
	  }
}

sub finish
{
	my $self = shift;

	$self->write_variant;
	return if $self->error;
	$self->send_done;
}

sub set_caller
{
	my $self = shift;

	$self->formfile("main", "setvariant.html");
	$self->formmap("variant", $self->{formdata}{variant});
	$self->print;
}

sub cancel
{
	my $self = shift;

	$self->formfile("main", "cancel.html");
	$self->print;
}

sub write_variant
{
	my $self = shift;
	my ($erp, $req, $config, $feat, $tranid, $opts, $cref);

	$erp = $self->erp_connect(escape => 0);
	$req = $erp->request("WRITE_PRODUCT_VARIANT_HEADER", {
		   generic_product	=> $self->{formdata}{product},
		   description		=> $self->{formdata}{variant_desc},
		   erpcustomer		=> "",
		   } );
        $self->erp_error, return unless $req;
	$tranid = $req->[0]{tranid};

	$opts = 0;
	for $config (@{$self->{formdata}{configuration}})
	  {
	  $cref = $config->{parent};

	  unless ($cref)
	    {
	    # No BOM stack, just write a single record for the main product
	    $req = $erp->request("WRITE_PRODUCT_VARIANT_STRUCTURE", {
		       tranid			=> $tranid,
		       option_set		=> $opts,
		       generic_product		=> $config->{product},
		       prod_config_level	=> $config->{level},
		       manf_prod		=> "",
		       pos_num			=> 0,
		       seq_num			=> 0,
		       entry_num		=> $config->{level},
		       } );
	    $self->erp_error, return unless $req;
	    }
	  else
	    {
	    # Write BOM stack information for Baan's structure data
	    my $save_cref = $config;
	    while ($cref)
	      {
	      $req = $erp->request("WRITE_PRODUCT_VARIANT_STRUCTURE", {
			 tranid			=> $tranid,
			 option_set		=> $opts,
			 generic_product	=> $config->{product},
			 prod_config_level	=> $config->{level},
			 manf_prod		=> $cref->{product},
			 pos_num		=> $save_cref->{pos},
			 seq_num		=> $save_cref->{seq},
			 entry_num		=> $cref->{level},
			 } );
	      $self->erp_error, return unless $req;

	      $save_cref = $cref;
	      $cref = $cref->{parent};
	      }
	    }

	  for $feat (@{$config->{features}->getList})
	    {
	    $req = $erp->request("WRITE_PRODUCT_VARIANT_OPTIONS", {
		      tranid		=> $tranid,
		      option_set	=> $opts,
		      seq_num		=> $feat->{seq},
		      feature		=> $feat->{name},
		      option		=> $feat->value,
		      } );
	    $self->erp_error, return unless $req;
	    }

	  $opts ++;
	  }

	$req = $erp->request("TRAN_COMMIT", { tranid => $tranid });
	$self->erp_error, return unless $req;
	$req = $erp->request("POST_PRODUCT_VARIANT", { tranid => $tranid } );
	$self->erp_error, return unless $req;
	$self->raise_error("pcfg_posterror", $req->[0]{status}), return
	    unless $req->[0]{status} == 0;
	$self->{formdata}{variant} = $req->[0]{variant};
}

sub load_constraint_package
{
	my $self = shift;
	my $cref = shift;

	# Attempt to get an instance of the constraint class for this
	# item; having no class is NOT considered to be an error
	# (unless there is a later attempt to define a constraint).
	my $constname = "configurator::constraints::IC_" . 
	    getObjectName(uc $cref->{product});
	eval "require $constname";
	unless ($@)
	  {
	  return if $cref->{constclass};
	  $cref->{constclass} = $constname->new(
	  	$self->{formdata}{globals},
		$cref->{features});
	  }
	else
	  {
	  unless ($@ =~ /^Can't locate /)
	    {
	    print STDERR "Unable to load constraint class: $constname\n";
	    die $@;
	    }
	  $cref->{constclass} = undef;
	  }
}

sub send_features
{
	my ($self, $btndone) = @_;
	my ($feat, $opt, $btnprev, $btnnext, $cref);

	$btnprev = $self->{formdata}{pos} > 0;
	$btnnext = $self->{formdata}{pos} < 
	    $#{$self->{formdata}{configuration}};
	$btndone = 1 unless $btnnext;
	$cref = $self->{formdata}{configuration}[$self->{formdata}{pos}];

	$self->response_open_envelope;
	print "comp:", escape($cref->{product}), 
	    ":", escape($cref->{desc}), 
	    ":", ($btnprev ? "1" : ''), 
	    ":", ($btnnext ? "1" : ''),
	    ":", ($btndone ? "1" : ''), "\n";
	for $feat (@{$cref->{features}->getList})
	  {
	  print "feat:", escape($feat->{name}),
	      ":", escape($feat->{desc}),
	      ":", ($feat->{optlist} ? "list" : "field"),
	      ":", $feat->{type};

	  print ":", (defined $feat->value ? $feat->value : "<null>");

	  print ":", ($feat->{constraint} ? $feat->{constraint}{name} : ""),
	        "\n";

	  if ($feat->{optlist})
	    {
	    for $opt (@{$feat->{optlist}})
	      {
	      print "opt:", escape($opt->{name}),
	          ":", $opt->{value}, "\n";
	      }
	    }
	  }
	$self->response_close_envelope;
}

sub send_done
{
	my $self = shift;
	die "variant number required" unless $self->{formdata}{variant};

	$self->response_open_envelope;
	print "done:", escape($self->{formdata}{product}),
	    ":", $self->{formdata}{variant}, "\n";
	$self->response_close_envelope;
}

sub send_error
{
	my $self = shift;
	my $msg = shift;
	$self->raise_error($msg, @_);
	$self->send_message;
}

sub send_fatal
{
	my $self = shift;
	my $msg = shift;
	$self->raise_error($msg, @_);
	$self->send_message("fatal");
}

sub send_message
{
	my $self = shift;
	my $class = shift;
	my ($msg);

	if ($self->error)
	  { 
	  $msg = $self->message || "Error Unknown";
	  $class = "error" unless $class;
	  }
	else
	  {
	  $msg = $self->message || "(message missing)";
	  $class = "info" unless $class;
	  }

	##$Text::Wrap::columns = 40;
	##$msg = wrap("", "", $msg);

	$self->response_open_envelope;
	# FIXME This should remove all \n characters
	chomp($msg);
	print "msg:", $class, ":", escape($msg), "\n";
	$self->response_close_envelope;
}

sub response_open_envelope
{
	print header(-type => "text/x-wise-config");
}

sub response_close_envelope
{
	print "end:\n";
}

## Non-method functions

sub escape
{
	my $str = shift;
	# This only needs to escape any ":" and "\" characters
	$str =~ s/(:|\\(?!\d\d\d))/\\$1/g;
	return $str;
}

sub getObjectName {
	my $name = shift;
	my $objname;

	($objname = $name) =~ s/\s+$//;
	$objname =~ s/_/__/g;
	$objname =~ s/([^\d\w_])/sprintf("_%x", ord($1))/ge;
	return $objname;
}

1;
