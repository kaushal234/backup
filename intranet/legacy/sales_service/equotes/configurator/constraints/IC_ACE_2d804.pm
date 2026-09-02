package configurator::constraints::IC_ACE_2d804;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_ACE_2d804",
      "item" => "ACE-804",
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

package configurator::constraints::IC_ACE_2d804::c_p001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_p001",
      "origname" => "p001",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_ACE_2d804::c_p002;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_p002",
      "origname" => "p002",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_ACE_2d804::c_p003;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_p003",
      "origname" => "p003",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_ACE_2d804::c_f001;

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

  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_TRLRTYPE = "";
  if( ((($f_TRAILER eq "Y")) && (((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD"))))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("TRLRTYPE", $f_TRLRTYPE);
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


package configurator::constraints::IC_ACE_2d804::c_f002;

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

  my $f_SLDTIRE = $self->{features}->getString("SLDTIRE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_SLDTIRE = "";
  if( ((($f_TRAILER eq "Y")) && (((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD"))))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("SLDTIRE", $f_SLDTIRE);
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


package configurator::constraints::IC_ACE_2d804::c_f003;

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

  my $f_TOWBRK = $self->{features}->getString("TOWBRK");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_SLDTIRE = $self->{features}->getString("SLDTIRE");

  $s_display = 0;
  $s_input = 0;
  $f_TOWBRK = "";
  if( ((((($f_TRAILER eq "Y")) && (($f_TRLRTYPE eq "FIFTH")))) && (($f_SLDTIRE eq "N"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("TOWBRK", $f_TOWBRK);
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


package configurator::constraints::IC_ACE_2d804::c_f004;

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


package configurator::constraints::IC_ACE_2d804::c_f005;

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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_ETHER = "";
  if( ((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "JD")))) || (($f_ENGINE eq "CU24"))) ) {
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


package configurator::constraints::IC_ACE_2d804::c_f006;

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


package configurator::constraints::IC_ACE_2d804::c_f007;

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


package configurator::constraints::IC_ACE_2d804::c_f008;

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

  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_FWSEP = "";
  if( ((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD"))) ) {
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


package configurator::constraints::IC_ACE_2d804::c_f009;

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


package configurator::constraints::IC_ACE_2d804::c_f010;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  $s_display = 0;
  $s_input = 0;
  $f_LFW = "";
  if( ((($f_LFSD eq "N")) && (($f_TRAILER eq "Y"))) ) {
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


package configurator::constraints::IC_ACE_2d804::c_f011;

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

  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFW = $self->{features}->getString("LFW");

  $s_display = 0;
  $s_input = 0;
  $f_LFCOLOR = "";
  if( ((($f_LFSD eq "Y")) || (($f_LFW eq "Y"))) ) {
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


package configurator::constraints::IC_ACE_2d804::c_f012;

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


package configurator::constraints::IC_ACE_2d804::c_f013;

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


package configurator::constraints::IC_ACE_2d804::c_f014;

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


package configurator::constraints::IC_ACE_2d804::c_f015;

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


package configurator::constraints::IC_ACE_2d804::c_f016;

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
}

sub validation
{
  my $self = shift;
  my $s_message = $self->{globals}->getString("message");

  $s_message = "PAINT";

  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_f017;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  $s_display = 0;
  $s_input = 0;
  $f_FUELHTR = "";
  if( ((((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) && (($f_UNITYPE eq "HEATCOOL"))) ) {
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


package configurator::constraints::IC_ACE_2d804::c_f018;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f018",
      "origname" => "f018",
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


package configurator::constraints::IC_ACE_2d804::c_m001;

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
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "FIFTH")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m002;

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

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m003;

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
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_SLDTIRE = $self->{features}->getString("SLDTIRE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "FIFTH")) ) {
    $s_validate = 0;
  }
  if( !(($f_SLDTIRE eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m004;

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
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_SLDTIRE = $self->{features}->getString("SLDTIRE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "ACKERMAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_SLDTIRE eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m006;

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


package configurator::constraints::IC_ACE_2d804::c_m007;

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


package configurator::constraints::IC_ACE_2d804::c_m008;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m009;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m010;

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


package configurator::constraints::IC_ACE_2d804::c_m011;

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


package configurator::constraints::IC_ACE_2d804::c_m012;

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

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m013;

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

  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m014;

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


package configurator::constraints::IC_ACE_2d804::c_m015;

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


package configurator::constraints::IC_ACE_2d804::c_m016;

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


package configurator::constraints::IC_ACE_2d804::c_m017;

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


package configurator::constraints::IC_ACE_2d804::c_m018;

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


package configurator::constraints::IC_ACE_2d804::c_m019;

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


package configurator::constraints::IC_ACE_2d804::c_m020;

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


package configurator::constraints::IC_ACE_2d804::c_m021;

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


package configurator::constraints::IC_ACE_2d804::c_m022;

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
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "IZ")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m023;

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
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "IZ")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m024;

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
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "IZ")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m025;

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
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "IZ")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m026;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "JD")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m027;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "JD")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m028;

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
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "JD")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m029;

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
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_ENGINE eq "JD")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m030;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m031;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m032;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m033;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m034;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m035;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m036;

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

  my $f_TOWBRK = $self->{features}->getString("TOWBRK");

  if( !(($f_TOWBRK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m037;

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

  my $f_SLDTIRE = $self->{features}->getString("SLDTIRE");

  if( !(($f_SLDTIRE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m038;

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

  my $f_AUTODEF = $self->{features}->getString("AUTODEF");

  if( !(($f_AUTODEF eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m040;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "110")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m041;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "220")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m042;

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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m043;

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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ETHER eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m044;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m045;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m046;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m047;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m048;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m049;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m050;

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

  my $f_TURBO = $self->{features}->getString("TURBO");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TURBO eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m051;

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

  my $f_TURBO = $self->{features}->getString("TURBO");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TURBO eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m052;

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

  my $f_TURBO = $self->{features}->getString("TURBO");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TURBO eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m053;

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

  my $f_TURBO = $self->{features}->getString("TURBO");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TURBO eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m054;

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

  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FWSEPTYP = $self->{features}->getString("FWSEPTYP");

  if( !(($f_FWSEP eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_FWSEPTYP eq "RACORHTR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m055;

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

  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FWSEPTYP = $self->{features}->getString("FWSEPTYP");

  if( !(($f_FWSEP eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_FWSEPTYP eq "RACOR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m056;

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

  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_FWSEPTYP = $self->{features}->getString("FWSEPTYP");

  if( !(($f_FWSEP eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_FWSEPTYP eq "DAVCO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m057;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m058;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m059;

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
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m060;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m062;

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


package configurator::constraints::IC_ACE_2d804::c_m063;

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


package configurator::constraints::IC_ACE_2d804::c_m064;

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


package configurator::constraints::IC_ACE_2d804::c_m065;

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


package configurator::constraints::IC_ACE_2d804::c_m066;

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


package configurator::constraints::IC_ACE_2d804::c_m067;

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


package configurator::constraints::IC_ACE_2d804::c_m068;

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


package configurator::constraints::IC_ACE_2d804::c_m069;

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


package configurator::constraints::IC_ACE_2d804::c_m070;

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


package configurator::constraints::IC_ACE_2d804::c_m071;

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


package configurator::constraints::IC_ACE_2d804::c_m072;

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


package configurator::constraints::IC_ACE_2d804::c_m073;

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


package configurator::constraints::IC_ACE_2d804::c_m074;

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

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m075;

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

  if( !(($f_PAINTSCH eq "1")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m076;

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


package configurator::constraints::IC_ACE_2d804::c_m077;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m078;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m079;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m080;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m081;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m082;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m083;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m084;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m085;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m086;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m087;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m088;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m089;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m090;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m091;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m092;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m093;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "ACKERMAN")) ) {
    $s_validate = 0;
  }
  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m094;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_SLDTIRE = $self->{features}->getString("SLDTIRE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "FIFTH")) ) {
    $s_validate = 0;
  }
  if( !(($f_SLDTIRE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m095;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");
  my $f_SLDTIRE = $self->{features}->getString("SLDTIRE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "ACKERMAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_SLDTIRE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m096;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m097;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m098;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m099;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m100;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m101;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m102;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m103;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m104;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m105;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m106;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m107;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m108;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m109;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_UNITYPE eq "COOL")) || (($f_FUELHTR eq "N")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m110;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_UNITYPE eq "COOL")) || (($f_FUELHTR eq "N")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m111;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(((($f_UNITYPE eq "COOL")) || (($f_FUELHTR eq "N")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m112;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");

  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(((($f_UNITYPE eq "COOL")) || (($f_FUELHTR eq "N")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m113;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m114;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m115;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m116;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_FUELHTR = $self->{features}->getString("FUELHTR");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELHTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m117;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m118;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m119;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "CU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m120;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");

  if( !(($f_ENGINE eq "DU24")) ) {
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


package configurator::constraints::IC_ACE_2d804::c_m121;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m122;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m123;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m124;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m125;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m126;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m127;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m128;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m129;

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

  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BHTRVOLT eq "110")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m130;

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

  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BHTRVOLT eq "220")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m131;

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

  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ETHER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m132;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m133;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "FLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m134;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m135;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "NONFLASH")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m136;

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

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m137;

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

  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BTYPE eq "ROTATE")) ) {
    $s_validate = 0;
  }
  if( !(($f_BCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m138;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m139;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFSD eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m140;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "A")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m141;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_LFW eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_LFCOLOR eq "R")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "CU24")) || (($f_ENGINE eq "DU24")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m142;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m143;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m144;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m145;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m146;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m147;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "COOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m148;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m149;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_UNITYPE = $self->{features}->getString("UNITYPE");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TRAILER eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITYPE eq "HEATCOOL")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m150;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_m005;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d804::c_p001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_p001",
      "origname" => "p001",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
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


package configurator::constraints::IC_ACE_2d804::c_p002;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_p002",
      "origname" => "p002",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
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


package configurator::constraints::IC_ACE_2d804::c_p003;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_p003",
      "origname" => "p003",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
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
