package configurator::constraints::IC_ASU_2d600;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_ASU_2d600",
      "item" => "ASU-600",
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

package configurator::constraints::IC_ASU_2d600::c_o001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o001",
      "origname" => "o001",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_f001;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  $s_display = 0;
  $s_input = 0;
  $f_ENGINE = "";
  if( ((((((((((((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) || (($f_OUTPUT eq "180")))) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) || (($f_OUTPUT eq "400"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ENGINE", $f_ENGINE);
  $self->{globals}->set("input", $s_input);
  $self->{globals}->set("display", $s_display);
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");
  my $s_message = $self->{globals}->getString("message");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( ((((((((((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) || (($f_OUTPUT eq "400")))) && (($f_ENGINE eq "DD")))) || (((((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) && (($f_ENGINE eq "DU")))))) || (((((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) && (($f_ENGINE eq "CU"))))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
    $s_message = "That engine/output combination is not available";
  }

  $self->{globals}->set("validate", $s_validate);
  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_f002;

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
  my $s_validate = $self->{globals}->getInteger("validate");
  my $s_message = $self->{globals}->getString("message");

  my $f_BTYPE = $self->{features}->getString("BTYPE");

  if( ((((($f_BTYPE eq "FLASH")) || (($f_BTYPE eq "NONFLASH")))) || (($f_BTYPE eq "ROTATE"))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
    $s_message = "That Beacon Type is not listed";
  }

  $self->{globals}->set("validate", $s_validate);
  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_f003;

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


package configurator::constraints::IC_ASU_2d600::c_f004;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");

  $s_display = 0;
  $s_input = 0;
  $f_LFW = "";
  if( ((($f_LFSD eq "N")) && (($f_LFRD eq "N"))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_f005;

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

  if( ((((($f_LFCOLOR eq "R")) && (($f_BCOLOR eq "R")))) || (((($f_LFCOLOR eq "A")) && (($f_BCOLOR eq "A"))))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_f006;

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

  my $f_FWSEPTYP = $self->{features}->getString("FWSEPTYP");
  my $f_FWSEP = $self->{features}->getString("FWSEP");

  $s_display = 0;
  $s_input = 0;
  $f_FWSEPTYP = "";
  if( ($f_FWSEP eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("FWSEPTYP", $f_FWSEPTYP);
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


package configurator::constraints::IC_ASU_2d600::c_f007;

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


package configurator::constraints::IC_ASU_2d600::c_f010;

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
  my $s_message = $self->{globals}->getString("message");

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");

  if( ((($f_HOSELGTH eq "50")) || (($f_HOSELGTH eq "60"))) ) {
    $s_message = "50 or 60 ft hoses are not recommended due to excessive
pressure drop";
  }

  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_f011;

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

  my $f_COUPLER = $self->{features}->getString("COUPLER");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");

  $s_display = 0;
  $s_input = 0;
  $f_COUPLER = "";
  if( ($f_AIRDELHS eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("COUPLER", $f_COUPLER);
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


package configurator::constraints::IC_ASU_2d600::c_f012;

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


package configurator::constraints::IC_ASU_2d600::c_f013;

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


package configurator::constraints::IC_ASU_2d600::c_f015;

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
  my $s_input = $self->{globals}->getInteger("input");
  my $s_display = $self->{globals}->getInteger("display");

  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");

  $s_display = 0;
  $s_input = 0;
  $f_LFRD = "";
  if( ($f_LFSD eq "N") ) {
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


package configurator::constraints::IC_ASU_2d600::c_f016;

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

  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  $s_display = 0;
  $s_input = 0;
  $f_FWSEP = "";
  if( ((($f_OUTPUT eq "400")) || (($f_OUTPUT eq "150"))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_f017;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f017",
      "origname" => "f017",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  $s_display = 0;
  $s_input = 0;
  $f_FUELHTR = "";
  if( ((((($f_OUTPUT eq "400")) || (((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))))) || (((($f_OUTPUT eq "150")) || (($f_OUTPUT eq "100"))))) ) {
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
  my $s_message = $self->{globals}->getString("message");

  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( ($f_FUELHTR eq "Y") ) {
    $s_message = "FUELHTR";
  }

  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_f019;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f019",
      "origname" => "f019",
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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_ETHER = "";
  if( ($f_ENGINE eq "DD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ETHER", $f_ETHER);
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


package configurator::constraints::IC_ASU_2d600::c_f020;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f020",
      "origname" => "f020",
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

  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  $s_display = 0;
  $s_input = 0;
  $f_COOLDOWN = "";
  if( ($f_OUTPUT eq "400") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("COOLDOWN", $f_COOLDOWN);
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


package configurator::constraints::IC_ASU_2d600::c_f021;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f021",
      "origname" => "f021",
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

  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  $s_display = 0;
  $s_input = 0;
  $f_LFBTYPE = "";
  if( ((((($f_LFSD eq "Y")) || (($f_LFRD eq "Y")))) || (($f_LFW eq "Y"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("LFBTYPE", $f_LFBTYPE);
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


package configurator::constraints::IC_ASU_2d600::c_f022;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f022",
      "origname" => "f022",
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

  my $f_WARMUP = $self->{features}->getString("WARMUP");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  $s_display = 0;
  $s_input = 0;
  $f_WARMUP = "";
  if( ($f_OUTPUT eq "400") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("WARMUP", $f_WARMUP);
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


package configurator::constraints::IC_ASU_2d600::c_m001;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m002;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m003;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m004;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m005;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m006;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m007;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m008;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m009;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m010;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m011;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m012;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m013;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m014;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m015;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m016;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m017;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m018;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m019;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m020;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m021;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m022;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m023;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m024;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m025;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m026;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m027;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m028;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m029;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m030;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m031;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m032;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m033;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m034;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m035;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m036;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m037;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m038;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m039;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m040;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m041;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m042;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "250")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m043;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m044;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m045;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m046;

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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m047;

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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m048;

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

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "100")) || (($f_ENGINE eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m049;

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

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m050;

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

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "100")) || (($f_ENGINE eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m051;

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

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m052;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "120")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m053;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "120")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m054;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "240")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m055;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "240")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m056;

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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m057;

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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m058;

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

  my $f_PRIMTYPE = $self->{features}->getString("PRIMTYPE");

  if( !(($f_PRIMTYPE eq "ELEC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m059;

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

  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_FUELHTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m060;

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

  my $f_FWSEPTYP = $self->{features}->getString("FWSEPTYP");

  if( !(($f_FWSEPTYP eq "RACOR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m061;

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

  my $f_FWSEPTYP = $self->{features}->getString("FWSEPTYP");

  if( !(($f_FWSEPTYP eq "RACORHTR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m062;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "30")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "ACE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m063;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "30")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "KAISER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m064;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "40")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "ACE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m065;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "40")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "KAISER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m066;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "50")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "ACE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m067;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "50")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "KAISER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m068;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "60")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "ACE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m069;

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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "60")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "KAISER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m070;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m071;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m072;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m073;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m074;

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

  my $f_PAINTSCH = $self->{features}->getString("PAINTSCH");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_PAINTSCH eq "1")) ) {
    $s_validate = 0;
  }
  if( !(((((((((((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "180")))) || (($f_OUTPUT eq "250")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) || (($f_OUTPUT eq "315")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m075;

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


package configurator::constraints::IC_ASU_2d600::c_m076;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m077;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m078;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m079;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m080;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m081;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m082;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m083;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m084;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m085;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m086;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m087;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m088;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m089;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m091;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m092;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m093;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m094;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m095;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m095",
      "origname" => "m095",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m096;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m096",
      "origname" => "m096",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m097;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m097",
      "origname" => "m097",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m098;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m098",
      "origname" => "m098",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m099;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m099",
      "origname" => "m099",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m100;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m100",
      "origname" => "m100",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m101;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m101",
      "origname" => "m101",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m102;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m102",
      "origname" => "m102",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m103;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m103",
      "origname" => "m103",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m104;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m104",
      "origname" => "m104",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m105;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m105",
      "origname" => "m105",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m106;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m106",
      "origname" => "m106",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m107;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m107",
      "origname" => "m107",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m108;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m108",
      "origname" => "m108",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m109;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m109",
      "origname" => "m109",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m110;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m110",
      "origname" => "m110",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m111;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m111",
      "origname" => "m111",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m112;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m112",
      "origname" => "m112",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m113;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m113",
      "origname" => "m113",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_CE = $self->{features}->getString("CE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m114;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m114",
      "origname" => "m114",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m115;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m115",
      "origname" => "m115",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_CE = $self->{features}->getString("CE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m116;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m116",
      "origname" => "m116",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m117;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m117",
      "origname" => "m117",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m118;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m118",
      "origname" => "m118",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_OUTPUT eq "150")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m119;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m119",
      "origname" => "m119",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m120;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m120",
      "origname" => "m120",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m121;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m121",
      "origname" => "m121",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m122;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m122",
      "origname" => "m122",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m123;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m123",
      "origname" => "m123",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "120")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m124;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m124",
      "origname" => "m124",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "240")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m125;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m125",
      "origname" => "m125",
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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m126;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m126",
      "origname" => "m126",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m127;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m127",
      "origname" => "m127",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m128;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m128",
      "origname" => "m128",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m129;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m129",
      "origname" => "m129",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m130;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m130",
      "origname" => "m130",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m131;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m131",
      "origname" => "m131",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m132;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m132",
      "origname" => "m132",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m133;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m133",
      "origname" => "m133",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m134;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m134",
      "origname" => "m134",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m135;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m135",
      "origname" => "m135",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m136;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m136",
      "origname" => "m136",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m137;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m137",
      "origname" => "m137",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m138;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m138",
      "origname" => "m138",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m139;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m139",
      "origname" => "m139",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m140;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m140",
      "origname" => "m140",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m141;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m141",
      "origname" => "m141",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m142;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m142",
      "origname" => "m142",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m143;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m143",
      "origname" => "m143",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m144;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m144",
      "origname" => "m144",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m145;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m145",
      "origname" => "m145",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m146;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m146",
      "origname" => "m146",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m147;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m147",
      "origname" => "m147",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m148;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m148",
      "origname" => "m148",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m149;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m149",
      "origname" => "m149",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m150;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m150",
      "origname" => "m150",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m151;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m151",
      "origname" => "m151",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m152;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m152",
      "origname" => "m152",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m153;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m153",
      "origname" => "m153",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m154;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m154",
      "origname" => "m154",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m155;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m155",
      "origname" => "m155",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m156;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m156",
      "origname" => "m156",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m157;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m157",
      "origname" => "m157",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m158;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m158",
      "origname" => "m158",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m159;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m159",
      "origname" => "m159",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m160;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m160",
      "origname" => "m160",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m161;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m161",
      "origname" => "m161",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m162;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m162",
      "origname" => "m162",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m163;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m163",
      "origname" => "m163",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "240")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m164;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m164",
      "origname" => "m164",
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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD2000")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m165;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m165",
      "origname" => "m165",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m166;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m166",
      "origname" => "m166",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m167;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m167",
      "origname" => "m167",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m168;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m168",
      "origname" => "m168",
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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m169;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m169",
      "origname" => "m169",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m170;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m170",
      "origname" => "m170",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m171;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m171",
      "origname" => "m171",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_FWSEP eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m172;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m172",
      "origname" => "m172",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m173;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m173",
      "origname" => "m173",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m174;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m174",
      "origname" => "m174",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m175;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m175",
      "origname" => "m175",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m176;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m176",
      "origname" => "m176",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m177;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m177",
      "origname" => "m177",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m178;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m178",
      "origname" => "m178",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m179;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m179",
      "origname" => "m179",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m180;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m180",
      "origname" => "m180",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_FWSEP eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m181;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m181",
      "origname" => "m181",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_OUTPUT eq "400")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m182;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m182",
      "origname" => "m182",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m183;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m183",
      "origname" => "m183",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m184;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m184",
      "origname" => "m184",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m185;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m185",
      "origname" => "m185",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m186;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m186",
      "origname" => "m186",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m187;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m187",
      "origname" => "m187",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m188;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m188",
      "origname" => "m188",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m189;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m189",
      "origname" => "m189",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m190;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m190",
      "origname" => "m190",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_m191;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m191",
      "origname" => "m191",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m192;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m192",
      "origname" => "m192",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m193;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m193",
      "origname" => "m193",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m194;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m194",
      "origname" => "m194",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m195;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m195",
      "origname" => "m195",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m196;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m196",
      "origname" => "m196",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m197;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m197",
      "origname" => "m197",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "120")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m198;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m198",
      "origname" => "m198",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "240")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m199;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m199",
      "origname" => "m199",
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

  my $f_GLPSTRLO = $self->{features}->getString("GLPSTRLO");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_GLPSTRLO eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m200;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m200",
      "origname" => "m200",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m201;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m201",
      "origname" => "m201",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m202;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m202",
      "origname" => "m202",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m203;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m203",
      "origname" => "m203",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m204;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m204",
      "origname" => "m204",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_PAINTSCH eq "1")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m205;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m205",
      "origname" => "m205",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m206;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m206",
      "origname" => "m206",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m207;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m207",
      "origname" => "m207",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m208;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m208",
      "origname" => "m208",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m209;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m209",
      "origname" => "m209",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m210;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m210",
      "origname" => "m210",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m211;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m211",
      "origname" => "m211",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m212;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m212",
      "origname" => "m212",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m213;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m213",
      "origname" => "m213",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "N")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m214;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m214",
      "origname" => "m214",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m215;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m215",
      "origname" => "m215",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m216;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m216",
      "origname" => "m216",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m217;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m217",
      "origname" => "m217",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m218;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m218",
      "origname" => "m218",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m219;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m219",
      "origname" => "m219",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD60")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m220;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m220",
      "origname" => "m220",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m221;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m221",
      "origname" => "m221",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m222;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m222",
      "origname" => "m222",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m223;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m223",
      "origname" => "m223",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m224;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m224",
      "origname" => "m224",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m225;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m225",
      "origname" => "m225",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m226;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m226",
      "origname" => "m226",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m227;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m227",
      "origname" => "m227",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m228;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m228",
      "origname" => "m228",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m229;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m229",
      "origname" => "m229",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m230;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m230",
      "origname" => "m230",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m231;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m231",
      "origname" => "m231",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m232;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m232",
      "origname" => "m232",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m233;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m233",
      "origname" => "m233",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m234;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m234",
      "origname" => "m234",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m235;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m235",
      "origname" => "m235",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "110")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m236;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m236",
      "origname" => "m236",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "220")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m237;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m237",
      "origname" => "m237",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m238;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m238",
      "origname" => "m238",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m239;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m239",
      "origname" => "m239",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m240;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m240",
      "origname" => "m240",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m241;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m241",
      "origname" => "m241",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m242;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m242",
      "origname" => "m242",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m243;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m243",
      "origname" => "m243",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m244;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m244",
      "origname" => "m244",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m245;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m245",
      "origname" => "m245",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m246;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m246",
      "origname" => "m246",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m247;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m247",
      "origname" => "m247",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m248;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m248",
      "origname" => "m248",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFSD eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m249;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m249",
      "origname" => "m249",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m250;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m250",
      "origname" => "m250",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFRD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m251;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m251",
      "origname" => "m251",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFRD eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m252;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m252",
      "origname" => "m252",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFRD eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m253;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m253",
      "origname" => "m253",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m254;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m254",
      "origname" => "m254",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_OUTPUT eq "150")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m255;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m255",
      "origname" => "m255",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_OUTPUT eq "400")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m256;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m256",
      "origname" => "m256",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_COOLDOWN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m257;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m257",
      "origname" => "m257",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_COOLDOWN eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m258;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m258",
      "origname" => "m258",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m259;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m259",
      "origname" => "m259",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_OUTPUT eq "150")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m260;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m260",
      "origname" => "m260",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");

  if( !(($f_OUTPUT eq "400")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m261;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m261",
      "origname" => "m261",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_COOLDOWN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m262;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m262",
      "origname" => "m262",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_COOLDOWN = $self->{features}->getString("COOLDOWN");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_COOLDOWN eq "Y")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m264;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m264",
      "origname" => "m264",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m265;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m265",
      "origname" => "m265",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m267;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m267",
      "origname" => "m267",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m268;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m268",
      "origname" => "m268",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m269;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m269",
      "origname" => "m269",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m270;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m270",
      "origname" => "m270",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m271;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m271",
      "origname" => "m271",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m272;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m272",
      "origname" => "m272",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m273;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m273",
      "origname" => "m273",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m274;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m274",
      "origname" => "m274",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m275;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m275",
      "origname" => "m275",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m276;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m276",
      "origname" => "m276",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m277;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m277",
      "origname" => "m277",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m278;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m278",
      "origname" => "m278",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m279;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m279",
      "origname" => "m279",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m280;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m280",
      "origname" => "m280",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m281;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m281",
      "origname" => "m281",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m282;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m282",
      "origname" => "m282",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m283;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m283",
      "origname" => "m283",
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

  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");

  if( !(($f_PNLCOVER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m284;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m284",
      "origname" => "m284",
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

  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_RUBRAIL eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m285;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m285",
      "origname" => "m285",
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

  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_RUBRAIL eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m286;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m286",
      "origname" => "m286",
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

  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_RUBRAIL eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m287;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m287",
      "origname" => "m287",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m288;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m288",
      "origname" => "m288",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m289;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m289",
      "origname" => "m289",
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
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m290;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m290",
      "origname" => "m290",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_ETHER = $self->{features}->getString("ETHER");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m291;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m291",
      "origname" => "m291",
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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "45")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "ACE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m292;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m292",
      "origname" => "m292",
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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "45")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "KAISER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m293;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m293",
      "origname" => "m293",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m294;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m294",
      "origname" => "m294",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m295;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m295",
      "origname" => "m295",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m296;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m296",
      "origname" => "m296",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m297;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m297",
      "origname" => "m297",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m298;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m298",
      "origname" => "m298",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m299;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m299",
      "origname" => "m299",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m300;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m300",
      "origname" => "m300",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m301;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m301",
      "origname" => "m301",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m302;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m302",
      "origname" => "m302",
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
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m303;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m303",
      "origname" => "m303",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFSD eq "Y")) || (($f_LFRD eq "Y")))) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m304;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m304",
      "origname" => "m304",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m305;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m305",
      "origname" => "m305",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m306;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m306",
      "origname" => "m306",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m307;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m307",
      "origname" => "m307",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m308;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m308",
      "origname" => "m308",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m309;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m309",
      "origname" => "m309",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m310;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m310",
      "origname" => "m310",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(((($f_LFRD eq "Y")) || (($f_LFSD eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m311;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m311",
      "origname" => "m311",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m312;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m312",
      "origname" => "m312",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m313;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m313",
      "origname" => "m313",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m314;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m314",
      "origname" => "m314",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m315;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m315",
      "origname" => "m315",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m316;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m316",
      "origname" => "m316",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m317;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m317",
      "origname" => "m317",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m318;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m318",
      "origname" => "m318",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m319;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m319",
      "origname" => "m319",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m320;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m320",
      "origname" => "m320",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m321;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m321",
      "origname" => "m321",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m322;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m322",
      "origname" => "m322",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(((((((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m323;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m323",
      "origname" => "m323",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m324;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m324",
      "origname" => "m324",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "FLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m325;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m325",
      "origname" => "m325",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m326;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m326",
      "origname" => "m326",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "NONFLASH")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m327;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m327",
      "origname" => "m327",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m328;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m328",
      "origname" => "m328",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFBTYPE = $self->{features}->getString("LFBTYPE");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFBTYPE eq "ROTATE")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m329;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m329",
      "origname" => "m329",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m330;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m330",
      "origname" => "m330",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m331;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m331",
      "origname" => "m331",
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

  my $f_LANG = $self->{features}->getString("LANG");

  if( !(($f_LANG eq "DAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m332;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m332",
      "origname" => "m332",
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

  my $f_LANG = $self->{features}->getString("LANG");

  if( !(($f_LANG eq "FR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m333;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m333",
      "origname" => "m333",
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

  my $f_LANG = $self->{features}->getString("LANG");

  if( !(($f_LANG eq "ITL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m334;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m334",
      "origname" => "m334",
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

  my $f_LANG = $self->{features}->getString("LANG");

  if( !(($f_LANG eq "SPA")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m335;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m335",
      "origname" => "m335",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m336;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m336",
      "origname" => "m336",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m337;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m337",
      "origname" => "m337",
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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "30")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "TLD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m338;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m338",
      "origname" => "m338",
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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "45")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "TLD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m339;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m339",
      "origname" => "m339",
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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "50")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "TLD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m340;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m340",
      "origname" => "m340",
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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "40")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "TLD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_m341;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m341",
      "origname" => "m341",
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

  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_HOSELGTH eq "60")) ) {
    $s_validate = 0;
  }
  if( !(($f_COUPLER eq "TLD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_quantity = $self->{globals}->getDouble("quantity");
  my $s_run_time = $self->{globals}->getInteger("run_time");
  my $s_price = $self->{globals}->getDouble("price");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( ((((($f_OUTPUT eq "200")) || (($f_OUTPUT eq "270")))) || (($f_OUTPUT eq "300"))) ) {
    $s_quantity = ($s_quantity * 2);
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }
  else {
    if( ($f_OUTPUT eq "400") ) {
      $s_quantity = ($s_quantity * 3);
      $s_run_time = ($s_run_time * 3);
      $s_price = ($s_price * 3);
    }
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ASU_2d600::c_o001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o001",
      "origname" => "o001",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o002;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o002",
      "origname" => "o002",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o003;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o003",
      "origname" => "o003",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o004;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o004",
      "origname" => "o004",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o005;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o005",
      "origname" => "o005",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o006;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o006",
      "origname" => "o006",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o007;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o007",
      "origname" => "o007",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o008;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o008",
      "origname" => "o008",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o009;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o009",
      "origname" => "o009",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o010;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o010",
      "origname" => "o010",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o011;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o011",
      "origname" => "o011",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o012;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o012",
      "origname" => "o012",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o013;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o013",
      "origname" => "o013",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o014;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o014",
      "origname" => "o014",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o015;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o015",
      "origname" => "o015",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o016;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o016",
      "origname" => "o016",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o017;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o017",
      "origname" => "o017",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o018;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o018",
      "origname" => "o018",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o019;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o019",
      "origname" => "o019",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o020;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o020",
      "origname" => "o020",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 600);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o021;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o021",
      "origname" => "o021",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 960);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 45);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o022;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o022",
      "origname" => "o022",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");
  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");
  my $f_PNLCOVER = $self->{features}->getString("PNLCOVER");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  if( ($f_EXTRATTN eq "Y") ) {
    $s_run_time = ($s_run_time + 960);
  }
  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_AIRDELHS eq "Y") ) {
    $s_run_time = ($s_run_time + 45);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 15);
  }
  if( ($f_PNLCOVER eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_RUBRAIL eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o023;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o023",
      "origname" => "o023",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o024;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o024",
      "origname" => "o024",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "100")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o025;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o025",
      "origname" => "o025",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o026;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o026",
      "origname" => "o026",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "150")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o027;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o027",
      "origname" => "o027",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o028;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o028",
      "origname" => "o028",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o029;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o029",
      "origname" => "o029",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o030;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o030",
      "origname" => "o030",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "180")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o031;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o031",
      "origname" => "o031",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o032;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o032",
      "origname" => "o032",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o033;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o033",
      "origname" => "o033",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o034;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o034",
      "origname" => "o034",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "200")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o035;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o035",
      "origname" => "o035",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o036;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o036",
      "origname" => "o036",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o037;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o037",
      "origname" => "o037",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o038;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o038",
      "origname" => "o038",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "270")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o039;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o039",
      "origname" => "o039",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o040;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o040",
      "origname" => "o040",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o041;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o041",
      "origname" => "o041",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o042;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o042",
      "origname" => "o042",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "300")) ) {
    $s_validate = 0;
  }
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o043;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o043",
      "origname" => "o043",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o044;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o044",
      "origname" => "o044",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFRD = $self->{features}->getString("LFRD");
  my $f_LFW = $self->{features}->getString("LFW");

  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_ETHER eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FUELHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 150);
  }
  if( ($f_LFSD eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_LFRD eq "Y") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_LFW eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ASU_2d600::c_o045;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o045",
      "origname" => "o045",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "100")) || (($f_OUTPUT eq "150")))) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_o046;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o046",
      "origname" => "o046",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_o047;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o047",
      "origname" => "o047",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_o048;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o048",
      "origname" => "o048",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_o049;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o049",
      "origname" => "o049",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "180")) || (($f_OUTPUT eq "200")))) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_o050;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o050",
      "origname" => "o050",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_o051;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o051",
      "origname" => "o051",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_o052;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o052",
      "origname" => "o052",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_o053;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o053",
      "origname" => "o053",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(((($f_OUTPUT eq "270")) || (($f_OUTPUT eq "300")))) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ASU_2d600::c_o054;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o054",
      "origname" => "o054",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_o055;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o055",
      "origname" => "o055",
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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "400")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DD")) ) {
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


package configurator::constraints::IC_ASU_2d600::c_m090;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m263;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m263",
      "origname" => "m263",
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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ASU_2d600::c_m266;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m266",
      "origname" => "m266",
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
}

sub parameter_substitution
{
  my $self = shift;
}

1;
