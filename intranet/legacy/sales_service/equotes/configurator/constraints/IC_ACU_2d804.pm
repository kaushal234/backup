package configurator::constraints::IC_ACU_2d804;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_ACU_2d804",
      "item" => "ACU-804",
      "globals" => $globals,
      "features" => $features,
      };
  bless $self,$class;
  return $self;
}

sub getConstraint
{
  my ($self,$name) = @_;
  return undef unless $name;

  my $pckname = ref($self) . "::c_" . $name;
  my $class = $pckname->new($self->{globals},$self->{features});
  return $class;
}

package configurator::constraints::IC_ACU_2d804::c_f001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f001",
      "origname" => "f001",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BEACON = $self->{features}->getString("BEACON");

  $s_display = 0;
  $s_input = 0;
  $f_BTYPE = "";
  if( ($f_BEACON eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("BTYPE", $f_BTYPE);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f002;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f002",
      "origname" => "f002",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_BEACON = $self->{features}->getString("BEACON");

  $s_display = 0;
  $s_input = 0;
  $f_BCOLOR = "";
  if( ($f_BEACON eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("BCOLOR", $f_BCOLOR);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f003;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f003",
      "origname" => "f003",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  $s_display = 0;
  $s_input = 0;
  $f_LFSD = "";
  if( ($f_TRAILER eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("LFSD", $f_LFSD);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f004;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f004",
      "origname" => "f004",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  $s_display = 0;
  $s_input = 0;
  $f_LFRD = "";
  if( ((($f_LFSD eq "N")) && (($f_TRAILER eq "Y"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("LFRD", $f_LFRD);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f005;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f005",
      "origname" => "f005",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  $s_display = 0;
  $s_input = 0;
  $f_LFW = "";
  if( ((((($f_LFSD eq "N")) && (($f_LFRD eq "N")))) && (($f_TRAILER eq "Y"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("LFW", $f_LFW);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f006;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f006",
      "origname" => "f006",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  $s_display = 0;
  $s_input = 0;
  $f_BTYPE2 = "";
  if( ((((($f_LFSD eq "Y")) || (($f_LFRD eq "Y")))) || (($f_LFW eq "Y"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("BTYPE2", $f_BTYPE2);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f007;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f007",
      "origname" => "f007",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  $s_display = 0;
  $s_input = 0;
  $f_LFCOLOR = "";
  if( ((((($f_LFSD eq "Y")) || (($f_LFRD eq "Y")))) || (($f_LFW eq "Y"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("LFCOLOR", $f_LFCOLOR);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");
  my $s_message = $self->{globals}->getString("message");

  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( ((((($f_LFCOLOR eq "R")) && (($f_BCOLOR eq "R")))) || (((($f_LFCOLOR eq "Y")) && (($f_BCOLOR eq "Y"))))) ) {
    $s_validate = 0;
    $s_message = "BEACON";
  }
  else {
    $s_validate = 1;
  }

  $self->{globals}->set("validate", $s_validate);
  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f008;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f008",
      "origname" => "f008",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");

  $s_display = 0;
  $s_input = 0;
  $f_BHTRVOLT = "";
  if( ($f_BLKHTR eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("BHTRVOLT", $f_BHTRVOLT);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f009;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f009",
      "origname" => "f009",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_FWSEP = "";
  if( ($f_ENGINE eq "CU") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("FWSEP", $f_FWSEP);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f010;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f010",
      "origname" => "f010",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_FUELHTR = "";
  if( ((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("FUELHTR", $f_FUELHTR);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f011;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f011",
      "origname" => "f011",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");

  $s_display = 0;
  $s_input = 0;
  $f_HOSETYPE = "";
  if( ($f_AIRDELHS eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("HOSETYPE", $f_HOSETYPE);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f012;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f012",
      "origname" => "f012",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");

  $s_display = 0;
  $s_input = 0;
  $f_INSLHOSE = "";
  if( ($f_AIRDELHS eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("INSLHOSE", $f_INSLHOSE);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f013;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f013",
      "origname" => "f013",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");

  $s_display = 0;
  $s_input = 0;
  $f_HOSELGTH = "";
  if( ($f_AIRDELHS eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("HOSELGTH", $f_HOSELGTH);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f014;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f014",
      "origname" => "f014",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_message = $self->{globals}->getString("message");

  my $f_LANG = $self->{features}->getString("LANG");

  if( ($f_LANG eq "ZZZ") ) {
    $s_message = "LANG";
  }

  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f015;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f015",
      "origname" => "f015",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_message = $self->{globals}->getString("message");

  my $f_PAINTCOL = $self->{features}->getString("PAINTCOL");

  if( ($f_PAINTCOL eq "ZZZ") ) {
    $s_message = "PAINT";
  }

  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_f016;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f016",
      "origname" => "f016",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_FORKPOCK = $self->{features}->getString("FORKPOCK");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  $s_display = 0;
  $s_input = 0;
  $f_FORKPOCK = "";
  if( ($f_TRAILER eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("FORKPOCK", $f_FORKPOCK);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m001",
      "origname" => "m001",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m002;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m002",
      "origname" => "m002",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m003;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m003",
      "origname" => "m003",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m004;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m004",
      "origname" => "m004",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m005;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m005",
      "origname" => "m005",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m006;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m006",
      "origname" => "m006",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m007;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m007",
      "origname" => "m007",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m008;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m008",
      "origname" => "m008",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m009;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m009",
      "origname" => "m009",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m010;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m010",
      "origname" => "m010",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m011;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m011",
      "origname" => "m011",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m012;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m012",
      "origname" => "m012",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m013;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m013",
      "origname" => "m013",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m014;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m014",
      "origname" => "m014",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m015;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m015",
      "origname" => "m015",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m016;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m016",
      "origname" => "m016",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m017;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m017",
      "origname" => "m017",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m018;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m018",
      "origname" => "m018",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m019;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m019",
      "origname" => "m019",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m020;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m020",
      "origname" => "m020",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m021;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m021",
      "origname" => "m021",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m022;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m022",
      "origname" => "m022",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m023;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m023",
      "origname" => "m023",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m024;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m024",
      "origname" => "m024",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m025;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m025",
      "origname" => "m025",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m026;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m026",
      "origname" => "m026",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m027;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m027",
      "origname" => "m027",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m028;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m028",
      "origname" => "m028",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m029;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m029",
      "origname" => "m029",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m030;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m030",
      "origname" => "m030",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m031;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m031",
      "origname" => "m031",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m032;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m032",
      "origname" => "m032",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m033;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m033",
      "origname" => "m033",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m034;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m034",
      "origname" => "m034",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m035;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m035",
      "origname" => "m035",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m036;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m036",
      "origname" => "m036",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m037;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m037",
      "origname" => "m037",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m038;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m038",
      "origname" => "m038",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m039;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m039",
      "origname" => "m039",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m040;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m040",
      "origname" => "m040",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m041;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m041",
      "origname" => "m041",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m042;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m042",
      "origname" => "m042",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m043;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m043",
      "origname" => "m043",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m044;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m044",
      "origname" => "m044",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m045;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m045",
      "origname" => "m045",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m046;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m046",
      "origname" => "m046",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m047;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m047",
      "origname" => "m047",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m048;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m048",
      "origname" => "m048",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m049;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m049",
      "origname" => "m049",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m050;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m050",
      "origname" => "m050",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m051;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m051",
      "origname" => "m051",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m052;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m052",
      "origname" => "m052",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m053;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m053",
      "origname" => "m053",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m054;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m054",
      "origname" => "m054",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m055;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m055",
      "origname" => "m055",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m056;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m056",
      "origname" => "m056",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m057;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m057",
      "origname" => "m057",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m058;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m058",
      "origname" => "m058",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "FALSH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m059;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m059",
      "origname" => "m059",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_BTYPE2 = $self->{features}->getString("BTYPE2");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE2 eq "FALSH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m060;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m060",
      "origname" => "m060",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BHTRVOLT eq "110")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m061;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m061",
      "origname" => "m061",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BHTRVOLT eq "110")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m062;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m062",
      "origname" => "m062",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BHTRVOLT eq "220")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m063;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m063",
      "origname" => "m063",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BHTRVOLT eq "220")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m064;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m064",
      "origname" => "m064",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_COOLDOWN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m065;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m065",
      "origname" => "m065",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_COOLDOWN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m066;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m066",
      "origname" => "m066",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_WARMUP = $self->{features}->getString("WARMUP");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_WARMUP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m067;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m067",
      "origname" => "m067",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_WARMUP = $self->{features}->getString("WARMUP");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_WARMUP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m068;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m068",
      "origname" => "m068",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FWSEP = $self->{features}->getString("FWSEP");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_FWSEP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m069;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m069",
      "origname" => "m069",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m070;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m070",
      "origname" => "m070",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m071;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m071",
      "origname" => "m071",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "30")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m072;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m072",
      "origname" => "m072",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "40")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m073;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m073",
      "origname" => "m073",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "50")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m074;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m074",
      "origname" => "m074",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m075;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m075",
      "origname" => "m075",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "30")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m076;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m076",
      "origname" => "m076",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "40")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m077;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m077",
      "origname" => "m077",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "50")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m078;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m078",
      "origname" => "m078",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "ROUND")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m079;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m079",
      "origname" => "m079",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "30")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m080;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m080",
      "origname" => "m080",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "40")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m081;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m081",
      "origname" => "m081",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "50")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m082;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m082",
      "origname" => "m082",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m083;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m083",
      "origname" => "m083",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "30")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m084;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m084",
      "origname" => "m084",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "40")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m085;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m085",
      "origname" => "m085",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "50")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m086;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m086",
      "origname" => "m086",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSETYPE = $self->{features}->getString("HOSETYPE");
  my $f_INSLHOSE = $self->{features}->getString("INSLHOSE");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSETYPE eq "FLAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_INSLHOSE eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_HOSELGTH eq "60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m087;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m087",
      "origname" => "m087",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m088;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m088",
      "origname" => "m088",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_PAINTSCH = $self->{features}->getString("PAINTSCH");

  if( !(($f_PAINTSCH eq "1")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m089;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m089",
      "origname" => "m089",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_PAINTSCH = $self->{features}->getString("PAINTSCH");

  if( !(($f_PAINTSCH eq "2")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m090;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m090",
      "origname" => "m090",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m091;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m091",
      "origname" => "m091",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m092;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m092",
      "origname" => "m092",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m093;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m093",
      "origname" => "m093",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACU_2d804::c_m094;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m094",
      "origname" => "m094",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_FORKPOCK = $self->{features}->getString("FORKPOCK");

  if( !(($f_FORKPOCK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}

1;
