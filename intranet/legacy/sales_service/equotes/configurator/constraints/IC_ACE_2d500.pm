package configurator::constraints::IC_ACE_2d500;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_ACE_2d500",
      "item" => "ACE-500",
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

package configurator::constraints::IC_ACE_2d500::c_f001;

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
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  $s_display = 0;
  $s_input = 0;
  $f_TRLRTYPE = "";
  if( ((($f_CAPACITY eq "160")) || (($f_CAPACITY eq "190"))) ) {
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


package configurator::constraints::IC_ACE_2d500::c_f002;

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

  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  $s_display = 0;
  $s_input = 0;
  $f_FWSEP = "";
  if( ($f_PRMMOVE eq "D") ) {
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


package configurator::constraints::IC_ACE_2d500::c_f003;

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


package configurator::constraints::IC_ACE_2d500::c_f004;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  $s_display = 0;
  $s_input = 0;
  $f_BLKHTR = "";
  if( ($f_PRMMOVE eq "D") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("BLKHTR", $f_BLKHTR);
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


package configurator::constraints::IC_ACE_2d500::c_f005;

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


package configurator::constraints::IC_ACE_2d500::c_f006;

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


package configurator::constraints::IC_ACE_2d500::c_f007;

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


package configurator::constraints::IC_ACE_2d500::c_f008;

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


package configurator::constraints::IC_ACE_2d500::c_f009;

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


package configurator::constraints::IC_ACE_2d500::c_f010;

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

  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  $s_display = 0;
  $s_input = 0;
  $f_UNITVOLT = "";
  if( ($f_PRMMOVE eq "E") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("UNITVOLT", $f_UNITVOLT);
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


package configurator::constraints::IC_ACE_2d500::c_f011;

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


package configurator::constraints::IC_ACE_2d500::c_f012;

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

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  $s_display = 0;
  $s_input = 0;
  $f_BEACON = "";
  if( ($f_PRMMOVE eq "D") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("BEACON", $f_BEACON);
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


package configurator::constraints::IC_ACE_2d500::c_f013;

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


package configurator::constraints::IC_ACE_2d500::c_m001;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m002;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m003;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }
  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m004;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m005;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "ACKERMAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m006;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "ACKERMAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m007;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "FIFTH")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m008;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_TRLRTYPE = $self->{features}->getString("TRLRTYPE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRLRTYPE eq "FIFTH")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m009;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m010;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m011;

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


package configurator::constraints::IC_ACE_2d500::c_m012;

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


package configurator::constraints::IC_ACE_2d500::c_m013;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "110")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m014;

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

  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_BHTRVOLT = $self->{features}->getString("BHTRVOLT");

  if( !(($f_BLKHTR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_BHTRVOLT eq "220")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m015;

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

  my $f_AUXAIR = $self->{features}->getString("AUXAIR");

  if( !(($f_AUXAIR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m016;

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

  my $f_LOWTEMP = $self->{features}->getString("LOWTEMP");

  if( !(($f_LOWTEMP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m017;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m018;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m019;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m020;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m021;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m022;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m023;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m024;

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

  my $f_AIRDELHS = $self->{features}->getString("AIRDELHS");
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m025;

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


package configurator::constraints::IC_ACE_2d500::c_m026;

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


package configurator::constraints::IC_ACE_2d500::c_m027;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m028;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m029;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m030;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }
  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m031;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }
  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m032;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }
  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m033;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m040;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m041;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m042;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m043;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m044;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "208")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m045;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "240")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m046;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "380")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m047;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "480")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m048;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m049;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m050;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m051;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_LOWTEMP = $self->{features}->getString("LOWTEMP");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_LOWTEMP eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m052;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_LOWTEMP = $self->{features}->getString("LOWTEMP");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }
  if( !(($f_LOWTEMP eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m053;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_LOWTEMP = $self->{features}->getString("LOWTEMP");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_LOWTEMP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m054;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_LOWTEMP = $self->{features}->getString("LOWTEMP");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }
  if( !(($f_LOWTEMP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m055;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m056;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m057;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m058;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m059;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "208")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m060;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "240")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m061;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "380")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m062;

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

  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");
  my $f_UNITVOLT = $self->{features}->getString("UNITVOLT");

  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }
  if( !(($f_UNITVOLT eq "480")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m063;

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


package configurator::constraints::IC_ACE_2d500::c_m064;

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


package configurator::constraints::IC_ACE_2d500::c_m065;

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


package configurator::constraints::IC_ACE_2d500::c_m066;

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


package configurator::constraints::IC_ACE_2d500::c_m067;

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


package configurator::constraints::IC_ACE_2d500::c_m068;

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


package configurator::constraints::IC_ACE_2d500::c_m069;

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


package configurator::constraints::IC_ACE_2d500::c_m070;

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

  my $f_LANG = $self->{features}->getString("LANG");

  if( !(($f_LANG eq "ENG")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m071;

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


package configurator::constraints::IC_ACE_2d500::c_m072;

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


package configurator::constraints::IC_ACE_2d500::c_m073;

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


package configurator::constraints::IC_ACE_2d500::c_m074;

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


package configurator::constraints::IC_ACE_2d500::c_m075;

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

  my $f_LANG = $self->{features}->getString("LANG");

  if( !(($f_LANG eq "SWE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m076;

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

  my $f_LANG = $self->{features}->getString("LANG");

  if( !(($f_LANG eq "ZZZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m077;

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
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m078;

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
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m079;

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
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_m080;

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
  my $f_HOSELGTH = $self->{features}->getString("HOSELGTH");
  my $f_COUPLER = $self->{features}->getString("COUPLER");

  if( !(($f_AIRDELHS eq "Y")) ) {
    $s_validate = 0;
  }
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
}


package configurator::constraints::IC_ACE_2d500::c_o001;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_AUXAIR = $self->{features}->getString("AUXAIR");

  if( ($f_AUXAIR eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o002;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_AUXAIR = $self->{features}->getString("AUXAIR");

  if( ($f_AUXAIR eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o003;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_AUXAIR = $self->{features}->getString("AUXAIR");

  if( ($f_AUXAIR eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o004;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_AUXAIR = $self->{features}->getString("AUXAIR");

  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_AUXAIR eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o005;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_AUXAIR = $self->{features}->getString("AUXAIR");

  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_AUXAIR eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o006;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");
  my $f_AUXAIR = $self->{features}->getString("AUXAIR");

  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_AUXAIR eq "Y") ) {
    $s_run_time = ($s_run_time + 90);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o007;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o008;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o009;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o010;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");

  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o011;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");

  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o012;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BEACON = $self->{features}->getString("BEACON");
  my $f_FWSEP = $self->{features}->getString("FWSEP");
  my $f_BLKHTR = $self->{features}->getString("BLKHTR");

  if( ($f_BEACON eq "Y") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ($f_FWSEP eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BLKHTR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_ACE_2d500::c_o013;

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


package configurator::constraints::IC_ACE_2d500::c_o014;

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


package configurator::constraints::IC_ACE_2d500::c_o015;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o016;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o017;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "E")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o018;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "160")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o019;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "190")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_o020;

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

  my $f_CAPACITY = $self->{features}->getString("CAPACITY");
  my $f_PRMMOVE = $self->{features}->getString("PRMMOVE");

  if( !(($f_CAPACITY eq "230")) ) {
    $s_validate = 0;
  }
  if( !(($f_PRMMOVE eq "D")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m034;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m035;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m036;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m037;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m038;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_ACE_2d500::c_m039;

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
}

sub parameter_substitution
{
  my $self = shift;
}

1;
