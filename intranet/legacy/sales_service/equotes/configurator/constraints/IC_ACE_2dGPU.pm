package configurator::constraints::IC_ACE_2dGPU;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_ACE_2dGPU",
      "item" => "ACE-GPU",
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

package configurator::constraints::IC_ACE_2dGPU::c_1;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_1",
      "origname" => "1",
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


package configurator::constraints::IC_ACE_2dGPU::c_2;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_2",
      "origname" => "2",
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


package configurator::constraints::IC_ACE_2dGPU::c_f001;

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
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");
  my $s_message = $self->{globals}->getString("message");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( ((((((((((((((($f_OUTPUT eq "090")) && (($f_GEN eq "KATO")))) && (((((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "DUEMR")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))))) || (((((($f_OUTPUT eq "090")) && (($f_GEN eq "MAR")))) && (((((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "DUEMR")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))))))) || (((((($f_OUTPUT eq "120")) && (($f_GEN eq "KATO")))) && (((((((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "DUEMR")))) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))))))) || (((((($f_OUTPUT eq "120")) && (($f_GEN eq "MAR")))) && (((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))) || (($f_ENGINE eq "DUEMR")))))))) || (((((($f_OUTPUT eq "130")) && (($f_GEN eq "KATO")))) && (((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU")))))))) || (((((($f_OUTPUT eq "140")) && (($f_GEN eq "KATO")))) && (((($f_ENGINE eq "CU")) || (($f_ENGINE eq "DU"))))))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
    $s_message = "That engine/generator/output combination is not available";
  }

  $self->{globals}->set("validate", $s_validate);
  $self->{globals}->set("message", $s_message);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_f002;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_f003;

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


package configurator::constraints::IC_ACE_2dGPU::c_f004;

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

  $s_display = 0;
  $s_input = 0;
  $f_LFW = "";
  if( ($f_LFSD eq "N") ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_f005;

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


package configurator::constraints::IC_ACE_2dGPU::c_f006;

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

  my $f_RAILTYPE = $self->{features}->getString("RAILTYPE");
  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");

  $s_display = 0;
  $s_input = 0;
  $f_RAILTYPE = "";
  if( ($f_RUBRAIL eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("RAILTYPE", $f_RAILTYPE);
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


package configurator::constraints::IC_ACE_2dGPU::c_f007;

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

  my $f_ETHER = $self->{features}->getString("ETHER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_ETHER = "";
  if( ((($f_ENGINE eq "CU")) || (($f_ENGINE eq "JD"))) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_f008;

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


package configurator::constraints::IC_ACE_2dGPU::c_f009;

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


package configurator::constraints::IC_ACE_2dGPU::c_f010;

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

  my $f_DRUMBRK = $self->{features}->getString("DRUMBRK");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  $s_display = 0;
  $s_input = 0;
  $f_DRUMBRK = "";
  if( ($f_TRAILER eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("DRUMBRK", $f_DRUMBRK);
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


package configurator::constraints::IC_ACE_2dGPU::c_f011;

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

  my $f_2NDOUT = $self->{features}->getString("2NDOUT");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  $s_display = 0;
  $s_input = 0;
  $f_2NDOUT = "";
  if( ($f_OUTPUT eq "090") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("2NDOUT", $f_2NDOUT);
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


package configurator::constraints::IC_ACE_2dGPU::c_f012;

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


package configurator::constraints::IC_ACE_2dGPU::c_f013;

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

  my $f_GENWARN = $self->{features}->getString("GENWARN");
  my $f_GEN = $self->{features}->getString("GEN");

  $s_display = 0;
  $s_input = 0;
  $f_GENWARN = "";
  if( ($f_GEN eq "KATO") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("GENWARN", $f_GENWARN);
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


package configurator::constraints::IC_ACE_2dGPU::c_f014;

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

  my $f_NBPT = $self->{features}->getString("NBPT");
  my $f_GEN = $self->{features}->getString("GEN");

  $s_display = 0;
  $s_input = 0;
  $f_NBPT = "";
  if( ($f_GEN eq "KATO") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("NBPT", $f_NBPT);
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


package configurator::constraints::IC_ACE_2dGPU::c_f015;

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

  my $f_28CABLE = $self->{features}->getString("28CABLE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  $s_display = 0;
  $s_input = 0;
  $f_28CABLE = "";
  if( ($f_28VTR eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("28CABLE", $f_28CABLE);
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


package configurator::constraints::IC_ACE_2dGPU::c_f016;

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


package configurator::constraints::IC_ACE_2dGPU::c_f017;

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


package configurator::constraints::IC_ACE_2dGPU::c_i001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i001",
      "origname" => "i001",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_ACE_2dGPU::c_i002;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i002",
      "origname" => "i002",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_ACE_2dGPU::c_i003;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i003",
      "origname" => "i003",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_i004;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i004",
      "origname" => "i004",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_DRUMBRK = $self->{features}->getString("DRUMBRK");

  if( !(($f_DRUMBRK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_i005;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i005",
      "origname" => "i005",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_i006;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i006",
      "origname" => "i006",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_i007;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i007",
      "origname" => "i007",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_i008;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i008",
      "origname" => "i008",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_i009;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_i009",
      "origname" => "i009",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m001;

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
  my $f_DRUMBRK = $self->{features}->getString("DRUMBRK");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( ((((((($f_TRAILER eq "Y")) && (($f_DRUMBRK eq "N")))) && (((((($f_OUTPUT eq "090")) || (($f_OUTPUT eq "120")))) || (($f_OUTPUT eq "130")))))) || (((((((($f_TRAILER eq "Y")) && (($f_DRUMBRK eq "N")))) && (($f_OUTPUT eq "140")))) && (($f_ENGINE eq "CU"))))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m002;

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
  my $f_DRUMBRK = $self->{features}->getString("DRUMBRK");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( ((((((($f_TRAILER eq "Y")) && (($f_DRUMBRK eq "Y")))) && (((((($f_OUTPUT eq "090")) || (($f_OUTPUT eq "120")))) || (($f_OUTPUT eq "130")))))) || (((((((($f_TRAILER eq "Y")) && (($f_DRUMBRK eq "Y")))) && (($f_OUTPUT eq "140")))) && (($f_ENGINE eq "CU"))))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m003;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_DRUMBRK = $self->{features}->getString("DRUMBRK");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_DRUMBRK eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m004;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_DRUMBRK = $self->{features}->getString("DRUMBRK");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_DRUMBRK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m005;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( ((((($f_OUTPUT eq "090")) && (((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))))) || (((((($f_OUTPUT eq "120")) && (($f_28VTR eq "N")))) && (((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD"))))))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m006;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( ((((($f_OUTPUT eq "090")) && (((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))))) || (((((($f_OUTPUT eq "120")) && (($f_28VTR eq "N")))) && (((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR"))))))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m007;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(((((($f_ENGINE eq "CU")) || (($f_ENGINE eq "IZ")))) || (($f_ENGINE eq "JD")))) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m008;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m009;

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
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m010;

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
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m011;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m012;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m013;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m014;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m015;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m016;

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
  my $f_28VTR = $self->{features}->getString("28VTR");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m017;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m018;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m019;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m020;

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

  if( !(((($f_OUTPUT eq "090")) || (($f_OUTPUT eq "120")))) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m021;

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

  if( !(((($f_OUTPUT eq "090")) || (($f_OUTPUT eq "120")))) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m022;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m023;

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

  if( !(($f_OUTPUT eq "120")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m024;

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

  if( !(($f_OUTPUT eq "120")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m025;

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
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m026;

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

  if( !(($f_OUTPUT eq "130")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m027;

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

  if( !(((($f_OUTPUT eq "130")) || (($f_OUTPUT eq "140")))) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m028;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m030;

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


package configurator::constraints::IC_ACE_2dGPU::c_m031;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m032;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m033;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m034;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m035;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m036;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m037;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m038;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m039;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m040;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m041;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m042;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m043;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m044;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m045;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m046;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m047;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m048;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m059;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m060;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m061;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m062;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m063;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m064;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m065;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m066;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m067;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m068;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m069;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m070;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m071;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m073;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m075;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m077;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m081;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m082;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m083;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m085;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m089;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m090;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m091;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m092;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m093;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m094;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m095;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m096;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m105;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m106;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m107;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m108;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m109;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m110;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m111;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m112;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m113;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m114;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m117;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m118;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m121;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m122;

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
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m125;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m126;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m130;

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

  my $f_2NDOUT = $self->{features}->getString("2NDOUT");

  if( !(($f_2NDOUT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m131;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m132;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m133;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m134;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m135;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m136;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_BTYPE = $self->{features}->getString("BTYPE");
  my $f_BCOLOR = $self->{features}->getString("BCOLOR");

  if( !(($f_BEACON eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m137;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m138;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "110")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m139;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "110")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m140;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "110")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m141;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
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


package configurator::constraints::IC_ACE_2dGPU::c_m142;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "220")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m143;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "220")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m144;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "220")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m145;

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


package configurator::constraints::IC_ACE_2dGPU::c_m146;

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


package configurator::constraints::IC_ACE_2dGPU::c_m147;

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


package configurator::constraints::IC_ACE_2dGPU::c_m148;

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

  my $f_DOWNEXH = $self->{features}->getString("DOWNEXH");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_DOWNEXH eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m149;

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

  my $f_DOWNEXH = $self->{features}->getString("DOWNEXH");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_DOWNEXH eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m151;

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

  my $f_DOWNEXH = $self->{features}->getString("DOWNEXH");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_DOWNEXH eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m152;

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

  my $f_DOWNEXH = $self->{features}->getString("DOWNEXH");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_DOWNEXH eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m153;

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


package configurator::constraints::IC_ACE_2dGPU::c_m154;

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


package configurator::constraints::IC_ACE_2dGPU::c_m155;

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

  my $f_FLOOD = $self->{features}->getString("FLOOD");

  if( !(($f_FLOOD eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m156;

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

  my $f_GENWARN = $self->{features}->getString("GENWARN");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_GENWARN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m157;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

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


package configurator::constraints::IC_ACE_2dGPU::c_m158;

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

  my $f_LFSD = $self->{features}->getString("LFSD");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

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


package configurator::constraints::IC_ACE_2dGPU::c_m159;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

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


package configurator::constraints::IC_ACE_2dGPU::c_m160;

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

  my $f_LFW = $self->{features}->getString("LFW");
  my $f_LFCOLOR = $self->{features}->getString("LFCOLOR");

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


package configurator::constraints::IC_ACE_2dGPU::c_m161;

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

  my $f_LCOOLSD = $self->{features}->getString("LCOOLSD");

  if( !(($f_LCOOLSD eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m162;

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

  my $f_NBPT = $self->{features}->getString("NBPT");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_NBPT eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((((($f_OUTPUT eq "120")) || (($f_OUTPUT eq "130")))) || (($f_OUTPUT eq "140")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m163;

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

  my $f_NBPT = $self->{features}->getString("NBPT");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");

  if( !(($f_NBPT eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_OUTPUT eq "090")) || (($f_OUTPUT eq "100")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m164;

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

  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");
  my $f_RAILTYPE = $self->{features}->getString("RAILTYPE");

  if( !(($f_RUBRAIL eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_RAILTYPE eq "BOTH")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m165;

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

  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");
  my $f_RAILTYPE = $self->{features}->getString("RAILTYPE");

  if( !(($f_RUBRAIL eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_RAILTYPE eq "REAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m166;

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

  my $f_RUBRAIL = $self->{features}->getString("RUBRAIL");
  my $f_RAILTYPE = $self->{features}->getString("RAILTYPE");

  if( !(($f_RUBRAIL eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_RAILTYPE eq "SIDE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m167;

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

  my $f_EXTRATTN = $self->{features}->getString("EXTRATTN");

  if( !(($f_EXTRATTN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m168;

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


package configurator::constraints::IC_ACE_2dGPU::c_m169;

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

  my $f_TURBO = $self->{features}->getString("TURBO");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_TURBO eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ENGINE eq "DU")) || (($f_ENGINE eq "DUEMR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m170;

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


package configurator::constraints::IC_ACE_2dGPU::c_m171;

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


package configurator::constraints::IC_ACE_2dGPU::c_m172;

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

  my $f_400CABLE = $self->{features}->getString("400CABLE");

  if( !(($f_400CABLE eq "30")) ) {
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
  my $f_2NDOUT = $self->{features}->getString("2NDOUT");

  if( ((((((($f_OUTPUT eq "120")) || (($f_OUTPUT eq "130")))) || (($f_OUTPUT eq "140")))) || (((($f_OUTPUT eq "090")) && (($f_2NDOUT eq "Y"))))) ) {
    $s_quantity = 2;
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ACE_2dGPU::c_m173;

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

  my $f_400CABLE = $self->{features}->getString("400CABLE");

  if( !(($f_400CABLE eq "40")) ) {
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
  my $f_2NDOUT = $self->{features}->getString("2NDOUT");

  if( ((((((($f_OUTPUT eq "120")) || (($f_OUTPUT eq "130")))) || (($f_OUTPUT eq "140")))) || (((($f_OUTPUT eq "090")) && (($f_2NDOUT eq "Y"))))) ) {
    $s_quantity = 2;
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ACE_2dGPU::c_m174;

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

  my $f_400CABLE = $self->{features}->getString("400CABLE");

  if( !(($f_400CABLE eq "50")) ) {
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
  my $f_2NDOUT = $self->{features}->getString("2NDOUT");

  if( ((((((($f_OUTPUT eq "120")) || (($f_OUTPUT eq "130")))) || (($f_OUTPUT eq "140")))) || (((($f_OUTPUT eq "090")) && (($f_2NDOUT eq "Y"))))) ) {
    $s_quantity = 2;
    $s_run_time = ($s_run_time * 2);
    $s_price = ($s_price * 2);
  }

  $self->{globals}->set("quantity", $s_quantity);
  $self->{globals}->set("run_time", $s_run_time);
  $self->{globals}->set("price", $s_price);
}


package configurator::constraints::IC_ACE_2dGPU::c_m175;

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

  my $f_28CABLE = $self->{features}->getString("28CABLE");

  if( !(($f_28CABLE eq "30")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m176;

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

  my $f_28CABLE = $self->{features}->getString("28CABLE");

  if( !(($f_28CABLE eq "40")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m177;

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

  my $f_28CABLE = $self->{features}->getString("28CABLE");

  if( !(($f_28CABLE eq "50")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m178;

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


package configurator::constraints::IC_ACE_2dGPU::c_m179;

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


package configurator::constraints::IC_ACE_2dGPU::c_m180;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m181;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m182;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m183;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m184;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m185;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m186;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m187;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m188;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m189;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m190;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m191;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m192;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m193;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m194;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m195;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m196;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m197;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m198;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m199;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m200;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m201;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m202;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m203;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m204;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m205;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m206;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m207;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m208;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m209;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m210;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m211;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m212;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m213;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m214;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m215;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "090")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m236;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m237;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m238;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m239;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m240;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m241;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m242;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m243;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m244;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m245;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m246;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m247;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m248;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m249;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m250;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m251;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m252;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m253;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m254;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m255;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m256;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m257;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m258;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m259;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m260;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m261;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m262;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m263;

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
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "MAR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m264;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m265;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m266;

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
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m267;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m274;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m275;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m276;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m277;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "130")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m284;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m285;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m286;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m287;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m292;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m293;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m294;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m295;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "140")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m300;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "90")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m301;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "90")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m302;

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

  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "90")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m303;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "90")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m304;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m305;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m306;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m307;

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
  my $f_GEN = $self->{features}->getString("GEN");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRAILER eq "N")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m308;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "90")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m309;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "90")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m310;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m311;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DUEMR")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m312;

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

  my $f_TRAILER = $self->{features}->getString("TRAILER");
  my $f_OUTPUT = $self->{features}->getString("OUTPUT");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( ((((($f_TRAILER eq "N")) && (((((($f_OUTPUT eq "090")) || (($f_OUTPUT eq "120")))) || (($f_OUTPUT eq "130")))))) || (((((($f_TRAILER eq "N")) && (($f_OUTPUT eq "140")))) && (($f_ENGINE eq "CU"))))) ) {
    $s_validate = 1;
  }
  else {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m313;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRAILER = $self->{features}->getString("TRAILER");

  if( !(($f_OUTPUT eq "140")) ) {
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


package configurator::constraints::IC_ACE_2dGPU::c_m314;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_m315;

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
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_28VTR = $self->{features}->getString("28VTR");
  my $f_GEN = $self->{features}->getString("GEN");

  if( !(($f_OUTPUT eq "120")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "JD")) ) {
    $s_validate = 0;
  }
  if( !(($f_28VTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_GEN eq "KATO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2dGPU::c_1;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_1",
      "origname" => "1",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
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


package configurator::constraints::IC_ACE_2dGPU::c_2;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_2",
      "origname" => "2",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
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


package configurator::constraints::IC_ACE_2dGPU::c_m129;

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
}

sub parameter_substitution
{
  my $self = shift;
}

1;
