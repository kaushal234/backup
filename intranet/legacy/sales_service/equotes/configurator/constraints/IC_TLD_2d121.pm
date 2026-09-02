package configurator::constraints::IC_TLD_2d121;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_TLD_2d121",
      "item" => "TLD-121",
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

package configurator::constraints::IC_TLD_2d121::c_f01;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f01",
      "origname" => "f01",
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

  my $f_ELEV = $self->{features}->getString("ELEV");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_ELEV = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ELEV", $f_ELEV);
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


package configurator::constraints::IC_TLD_2d121::c_f02;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f02",
      "origname" => "f02",
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
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_ENGINE = "";
  if( ($f_CONFIG eq "STD") ) {
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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_f03;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f03",
      "origname" => "f03",
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

  my $f_PUMP = $self->{features}->getString("PUMP");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_PUMP = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("PUMP", $f_PUMP);
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


package configurator::constraints::IC_TLD_2d121::c_f04;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f04",
      "origname" => "f04",
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

  my $f_FUELTK = $self->{features}->getString("FUELTK");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_FUELTK = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("FUELTK", $f_FUELTK);
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


package configurator::constraints::IC_TLD_2d121::c_f05;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f05",
      "origname" => "f05",
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

  my $f_OPCONS = $self->{features}->getString("OPCONS");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_OPCONS = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("OPCONS", $f_OPCONS);
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


package configurator::constraints::IC_TLD_2d121::c_f06;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f06",
      "origname" => "f06",
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

  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_JOYSTK = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("JOYSTK", $f_JOYSTK);
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


package configurator::constraints::IC_TLD_2d121::c_f07;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f07",
      "origname" => "f07",
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

  my $f_GUARDR = $self->{features}->getString("GUARDR");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_GUARDR = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("GUARDR", $f_GUARDR);
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


package configurator::constraints::IC_TLD_2d121::c_f08;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f08",
      "origname" => "f08",
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

  my $f_WEATHR = $self->{features}->getString("WEATHR");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_WEATHR = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("WEATHR", $f_WEATHR);
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


package configurator::constraints::IC_TLD_2d121::c_f09;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f09",
      "origname" => "f09",
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

  my $f_LANGU = $self->{features}->getString("LANGU");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_LANGU = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("LANGU", $f_LANGU);
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


package configurator::constraints::IC_TLD_2d121::c_f10;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f10",
      "origname" => "f10",
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

  my $f_CUSTOPT = $self->{features}->getString("CUSTOPT");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_CUSTOPT = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("CUSTOPT", $f_CUSTOPT);
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


package configurator::constraints::IC_TLD_2d121::c_f11;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f11",
      "origname" => "f11",
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

  my $f_OVERSEAS = $self->{features}->getString("OVERSEAS");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_OVERSEAS = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("OVERSEAS", $f_OVERSEAS);
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


package configurator::constraints::IC_TLD_2d121::c_f12;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f12",
      "origname" => "f12",
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

  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_ADDOPT = "";
  if( ($f_CONFIG eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ADDOPT", $f_ADDOPT);
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


package configurator::constraints::IC_TLD_2d121::c_f13;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f13",
      "origname" => "f13",
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

  my $f_ZPROTAN = $self->{features}->getString("ZPROTAN");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZPROTAN = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZPROTAN", $f_ZPROTAN);
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


package configurator::constraints::IC_TLD_2d121::c_f14;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f14",
      "origname" => "f14",
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

  my $f_ZTACH = $self->{features}->getString("ZTACH");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZTACH = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZTACH", $f_ZTACH);
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


package configurator::constraints::IC_TLD_2d121::c_f15;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f15",
      "origname" => "f15",
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

  my $f_Z110COLD = $self->{features}->getString("Z110COLD");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_Z110COLD = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("Z110COLD", $f_Z110COLD);
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


package configurator::constraints::IC_TLD_2d121::c_f16;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f16",
      "origname" => "f16",
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

  my $f_Z220COLD = $self->{features}->getString("Z220COLD");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_Z220COLD = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("Z220COLD", $f_Z220COLD);
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


package configurator::constraints::IC_TLD_2d121::c_f17;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f17",
      "origname" => "f17",
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

  my $f_ZARTIC = $self->{features}->getString("ZARTIC");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZARTIC = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZARTIC", $f_ZARTIC);
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


package configurator::constraints::IC_TLD_2d121::c_f18;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f18",
      "origname" => "f18",
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

  my $f_ZSPARK = $self->{features}->getString("ZSPARK");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZSPARK = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZSPARK", $f_ZSPARK);
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


package configurator::constraints::IC_TLD_2d121::c_f19;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f19",
      "origname" => "f19",
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

  my $f_ZARMBUMP = $self->{features}->getString("ZARMBUMP");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZARMBUMP = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZARMBUMP", $f_ZARMBUMP);
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


package configurator::constraints::IC_TLD_2d121::c_f20;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f20",
      "origname" => "f20",
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

  my $f_Z2DOOR = $self->{features}->getString("Z2DOOR");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_Z2DOOR = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("Z2DOOR", $f_Z2DOOR);
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


package configurator::constraints::IC_TLD_2d121::c_f21;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f21",
      "origname" => "f21",
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

  my $f_ZENLIGHT = $self->{features}->getString("ZENLIGHT");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZENLIGHT = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZENLIGHT", $f_ZENLIGHT);
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


package configurator::constraints::IC_TLD_2d121::c_f22;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f22",
      "origname" => "f22",
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

  my $f_ZAUTOLEV = $self->{features}->getString("ZAUTOLEV");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZAUTOLEV = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZAUTOLEV", $f_ZAUTOLEV);
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


package configurator::constraints::IC_TLD_2d121::c_f23;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f23",
      "origname" => "f23",
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

  my $f_ZGREYCON = $self->{features}->getString("ZGREYCON");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZGREYCON = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZGREYCON", $f_ZGREYCON);
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


package configurator::constraints::IC_TLD_2d121::c_f24;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f24",
      "origname" => "f24",
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

  my $f_ZAMB = $self->{features}->getString("ZAMB");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZAMB = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZAMB", $f_ZAMB);
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


package configurator::constraints::IC_TLD_2d121::c_f25;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f25",
      "origname" => "f25",
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

  my $f_ZHORN = $self->{features}->getString("ZHORN");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZHORN = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZHORN", $f_ZHORN);
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


package configurator::constraints::IC_TLD_2d121::c_f26;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f26",
      "origname" => "f26",
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

  my $f_ZFLBRIDG = $self->{features}->getString("ZFLBRIDG");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZFLBRIDG = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZFLBRIDG", $f_ZFLBRIDG);
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


package configurator::constraints::IC_TLD_2d121::c_f27;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f27",
      "origname" => "f27",
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

  my $f_ZFLELEV = $self->{features}->getString("ZFLELEV");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZFLELEV = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZFLELEV", $f_ZFLELEV);
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


package configurator::constraints::IC_TLD_2d121::c_f28;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f28",
      "origname" => "f28",
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

  my $f_ZREBRSWI = $self->{features}->getString("ZREBRSWI");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZREBRSWI = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZREBRSWI", $f_ZREBRSWI);
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


package configurator::constraints::IC_TLD_2d121::c_m001;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m003;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_CONFIG eq "STD")) ) {
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


package configurator::constraints::IC_TLD_2d121::c_m004;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CAT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m005;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_PUMP = $self->{features}->getString("PUMP");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_PUMP eq "REX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m006;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_PUMP = $self->{features}->getString("PUMP");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_PUMP eq "KAWA")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m007;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_FUELTK = $self->{features}->getString("FUELTK");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "60")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m008;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_FUELTK = $self->{features}->getString("FUELTK");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "60")) ) {
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


package configurator::constraints::IC_TLD_2d121::c_m009;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_OPCONS = $self->{features}->getString("OPCONS");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "POW")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m010;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ELEV eq "SR")) || (($f_ELEV eq "DR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m011;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "DR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m012;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "SR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m013;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "STD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m016;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_LANGU = $self->{features}->getString("LANGU");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_LANGU eq "ZZ")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m017;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_WEATHR = $self->{features}->getString("WEATHR");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_WEATHR eq "REGULAR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m018;

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

  my $f_OVERSEAS = $self->{features}->getString("OVERSEAS");

  if( !(($f_OVERSEAS eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m020;

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

  my $f_ZPROTAN = $self->{features}->getString("ZPROTAN");

  if( !(($f_ZPROTAN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m021;

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

  my $f_ZTACH = $self->{features}->getString("ZTACH");

  if( !(($f_ZTACH eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m022;

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

  my $f_Z110COLD = $self->{features}->getString("Z110COLD");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_Z110COLD eq "Y")) ) {
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


package configurator::constraints::IC_TLD_2d121::c_m023;

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

  my $f_Z220COLD = $self->{features}->getString("Z220COLD");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_Z220COLD eq "Y")) ) {
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


package configurator::constraints::IC_TLD_2d121::c_m024;

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

  my $f_ZARTIC = $self->{features}->getString("ZARTIC");

  if( !(($f_ZARTIC eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m025;

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

  my $f_ZSPARK = $self->{features}->getString("ZSPARK");

  if( !(($f_ZSPARK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m026;

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

  my $f_ZBUMPER = $self->{features}->getString("ZBUMPER");

  if( !(($f_ZBUMPER eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m027;

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

  my $f_Z2DOOR = $self->{features}->getString("Z2DOOR");

  if( !(($f_Z2DOOR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m028;

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

  my $f_ZENLIGHT = $self->{features}->getString("ZENLIGHT");

  if( !(($f_ZENLIGHT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m029;

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

  my $f_ZAUTOLEV = $self->{features}->getString("ZAUTOLEV");

  if( !(($f_ZAUTOLEV eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m030;

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

  my $f_ZGREYCON = $self->{features}->getString("ZGREYCON");

  if( !(($f_ZGREYCON eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m031;

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

  my $f_ZAMB = $self->{features}->getString("ZAMB");

  if( !(($f_ZAMB eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m032;

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

  my $f_ZHORN = $self->{features}->getString("ZHORN");

  if( !(($f_ZHORN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m033;

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

  my $f_ZFLELEV = $self->{features}->getString("ZFLELEV");
  my $f_OPCONS = $self->{features}->getString("OPCONS");

  if( !(($f_ZFLELEV eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "POW")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m034;

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

  my $f_ZFLBRIDG = $self->{features}->getString("ZFLBRIDG");

  if( !(($f_ZFLBRIDG eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m035;

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

  my $f_ZREBRSWI = $self->{features}->getString("ZREBRSWI");

  if( !(($f_ZREBRSWI eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m037;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_FUELTK = $self->{features}->getString("FUELTK");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "60")) ) {
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


package configurator::constraints::IC_TLD_2d121::c_m038;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_FUELTK = $self->{features}->getString("FUELTK");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "60")) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "CAT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m002;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m014;

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
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d121::c_m036;

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

1;
