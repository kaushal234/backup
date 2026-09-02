package configurator::constraints::IC_NBL;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_NBL",
      "item" => "NBL",
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

package configurator::constraints::IC_NBL::c_o001;

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
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");
  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");

  if( ((($f_BOOM_OPT eq "CAN")) || (($f_BOOM_OPT eq "CC"))) ) {
    $s_run_time = ($s_run_time + 960);
  }
  if( ((((($f_BOOM_OPT eq "CT")) || (($f_BOOM_OPT eq "FC")))) || (($f_BOOM_OPT eq "TC"))) ) {
    $s_run_time = ($s_run_time + 480);
  }
  if( ($f_CABIN eq "Y") ) {
    $s_run_time = ($s_run_time + 840);
  }
  if( ($f_CABDOOR eq "Y") ) {
    $s_run_time = ($s_run_time + 960);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_NBL::c_o002;

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
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");
  my $f_LIFTSPD = $self->{features}->getString("LIFTSPD");
  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_SEATBELT = $self->{features}->getString("SEATBELT");
  my $f_STABLZR = $self->{features}->getString("STABLZR");
  my $f_DECKPROT = $self->{features}->getString("DECKPROT");
  my $f_WOODBUMP = $self->{features}->getString("WOODBUMP");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( ($f_BOOM_OPT eq "CAN") ) {
    $s_run_time = ($s_run_time + 480);
  }
  if( ((((($f_BOOM_OPT eq "CC")) || (($f_BOOM_OPT eq "CT")))) || (($f_BOOM_OPT eq "FT"))) ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_BOOM_OPT eq "FC") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BOOM_OPT eq "TC") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_BOOM_OPT eq "TT") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LIFTSPD eq "Y") ) {
    $s_run_time = ($s_run_time + 840);
  }
  if( ($f_CABIN eq "Y") ) {
    $s_run_time = ($s_run_time + 360);
  }
  if( ($f_CABDOOR eq "Y") ) {
    $s_run_time = ($s_run_time + 240);
  }
  if( ($f_SEATBELT eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_STABLZR eq "STAB") ) {
    $s_run_time = ($s_run_time + 360);
  }
  if( ($f_STABLZR eq "MAN") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_DECKPROT eq "ALUM") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_DECKPROT eq "TANK") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_WOODBUMP eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_THROTTLE eq "MAN") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_NBL::c_o003;

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
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");
  my $f_STABLZR = $self->{features}->getString("STABLZR");
  my $f_LFCTLBOX = $self->{features}->getString("LFCTLBOX");
  my $f_LRCTLBOX = $self->{features}->getString("LRCTLBOX");
  my $f_WORKLITE = $self->{features}->getString("WORKLITE");
  my $f_PARKBRK = $self->{features}->getString("PARKBRK");

  if( ($f_AIRFAN eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ((($f_CABHEAT eq "DEVICE")) || (($f_CABHEAT eq "ELEC"))) ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_STABLZR eq "STAB") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ((($f_LFCTLBOX eq "EMER")) || (($f_LFCTLBOX eq "FIX"))) ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_LRCTLBOX eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_WORKLITE eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_PARKBRK eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_NBL::c_f001;

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

  my $f_TRANS = $self->{features}->getString("TRANS");
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_TRANS = "";
  if( ($f_ENGINE eq "IZ") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("TRANS", $f_TRANS);
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


package configurator::constraints::IC_NBL::c_f002;

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

  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_CABIN = $self->{features}->getString("CABIN");

  $s_display = 0;
  $s_input = 0;
  $f_CABDOOR = "";
  if( ($f_CABIN eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("CABDOOR", $f_CABDOOR);
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


package configurator::constraints::IC_NBL::c_f003;

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

  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_CABIN = $self->{features}->getString("CABIN");

  $s_display = 0;
  $s_input = 0;
  $f_WINDSCRN = "";
  if( ($f_CABIN eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("WINDSCRN", $f_WINDSCRN);
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


package configurator::constraints::IC_NBL::c_f004;

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

  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABIN = $self->{features}->getString("CABIN");

  $s_display = 0;
  $s_input = 0;
  $f_AIRFAN = "";
  if( ($f_CABIN eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("AIRFAN", $f_AIRFAN);
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


package configurator::constraints::IC_NBL::c_f005;

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

  my $f_CABHEAT = $self->{features}->getString("CABHEAT");
  my $f_CABIN = $self->{features}->getString("CABIN");

  $s_display = 0;
  $s_input = 0;
  $f_CABHEAT = "";
  if( ($f_CABIN eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("CABHEAT", $f_CABHEAT);
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


package configurator::constraints::IC_NBL::c_f006;

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

  my $f_BEACONSW = $self->{features}->getString("BEACONSW");
  my $f_BEACON = $self->{features}->getString("BEACON");

  $s_display = 0;
  $s_input = 0;
  $f_BEACONSW = "";
  if( ($f_BEACON eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("BEACONSW", $f_BEACONSW);
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


package configurator::constraints::IC_NBL::c_m001;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "Z")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "MAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m002;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "P")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "MAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m003;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "Z")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "FOOT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m004;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "P")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "FOOT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m005;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "MAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m006;

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
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "FOOT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m007;

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

  my $f_FUELTANK = $self->{features}->getString("FUELTANK");

  if( !(($f_FUELTANK eq "45")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m008;

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

  my $f_FUELTANK = $self->{features}->getString("FUELTANK");

  if( !(($f_FUELTANK eq "65")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m009;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "CAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m010;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "FT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m011;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "FC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m012;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "TT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m013;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "TC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m014;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "CT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m015;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "CC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m016;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "8")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m017;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "CAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m018;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "FT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m019;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "FC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m020;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "TT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m021;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "TC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m022;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "CT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m023;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "CC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m024;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m025;

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

  my $f_BOOMBUMP = $self->{features}->getString("BOOMBUMP");

  if( !(($f_BOOMBUMP eq "90")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m026;

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

  my $f_BOOMBUMP = $self->{features}->getString("BOOMBUMP");

  if( !(($f_BOOMBUMP eq "145")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m027;

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

  my $f_SEATBELT = $self->{features}->getString("SEATBELT");
  my $f_SSTSEAT = $self->{features}->getString("SSTSEAT");

  if( !(($f_SEATBELT eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_SSTSEAT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m028;

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

  my $f_SEATBELT = $self->{features}->getString("SEATBELT");
  my $f_SSTSEAT = $self->{features}->getString("SSTSEAT");

  if( !(($f_SEATBELT eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_SSTSEAT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m029;

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

  my $f_LIFTSPD = $self->{features}->getString("LIFTSPD");

  if( !(($f_LIFTSPD eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m030;

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

  my $f_LIFTSPD = $self->{features}->getString("LIFTSPD");

  if( !(($f_LIFTSPD eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m031;

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

  my $f_CABIN = $self->{features}->getString("CABIN");

  if( !(($f_CABIN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m032;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m033;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m034;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m035;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m036;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "ELEC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m037;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m038;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m039;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "DEVICE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m040;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m041;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m042;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "ELEC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m043;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "ELEC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m044;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "ELEC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m045;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "DEVICE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m046;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "N")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "DEVICE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m047;

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

  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");

  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABDOOR eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_WINDSCRN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_AIRFAN eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABHEAT eq "DEVICE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m048;

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

  my $f_STABLZR = $self->{features}->getString("STABLZR");

  if( !(($f_STABLZR eq "STAB")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m049;

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

  my $f_STABLZR = $self->{features}->getString("STABLZR");

  if( !(($f_STABLZR eq "MAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m050;

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

  my $f_STABLZR = $self->{features}->getString("STABLZR");

  if( !(($f_STABLZR eq "NONE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m051;

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

  my $f_DECKPROT = $self->{features}->getString("DECKPROT");

  if( !(($f_DECKPROT eq "ALUM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m052;

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

  my $f_DECKPROT = $self->{features}->getString("DECKPROT");

  if( !(($f_DECKPROT eq "TANK")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m053;

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

  my $f_LFCTLBOX = $self->{features}->getString("LFCTLBOX");

  if( !(($f_LFCTLBOX eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m054;

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

  my $f_LFCTLBOX = $self->{features}->getString("LFCTLBOX");

  if( !(($f_LFCTLBOX eq "EMER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m055;

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

  my $f_LRCTLBOX = $self->{features}->getString("LRCTLBOX");

  if( !(($f_LRCTLBOX eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m056;

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

  my $f_WORKLITE = $self->{features}->getString("WORKLITE");

  if( !(($f_WORKLITE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m057;

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

  my $f_PARKBRK = $self->{features}->getString("PARKBRK");

  if( !(($f_PARKBRK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m058;

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

  my $f_WOODBUMP = $self->{features}->getString("WOODBUMP");

  if( !(($f_WOODBUMP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m059;

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

  my $f_FIREXTNG = $self->{features}->getString("FIREXTNG");

  if( !(($f_FIREXTNG eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m060;

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

  my $f_SCOTCH = $self->{features}->getString("SCOTCH");

  if( !(($f_SCOTCH eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m061;

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

  my $f_LRCTLBOX = $self->{features}->getString("LRCTLBOX");

  if( !(($f_LRCTLBOX eq "EMER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m062;

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

  my $f_RFCTLBOX = $self->{features}->getString("RFCTLBOX");

  if( !(($f_RFCTLBOX eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m063;

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

  my $f_RFCTLBOX = $self->{features}->getString("RFCTLBOX");

  if( !(($f_RFCTLBOX eq "MOV")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m064;

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

  my $f_PARKLOCK = $self->{features}->getString("PARKLOCK");

  if( !(($f_PARKLOCK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m065;

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

  my $f_FIREBRKT = $self->{features}->getString("FIREBRKT");

  if( !(($f_FIREBRKT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m066;

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
  my $f_CABIN = $self->{features}->getString("CABIN");

  if( !(($f_BEACON eq "AF")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABIN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m067;

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
  my $f_CABIN = $self->{features}->getString("CABIN");

  if( !(($f_BEACON eq "AF")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m068;

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
  my $f_CABIN = $self->{features}->getString("CABIN");

  if( !(($f_BEACON eq "AR")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABIN eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m069;

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
  my $f_CABIN = $self->{features}->getString("CABIN");

  if( !(($f_BEACON eq "AR")) ) {
    $s_validate = 0;
  }
  if( !(($f_CABIN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m070;

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

  my $f_BEACONSW = $self->{features}->getString("BEACONSW");

  if( !(($f_BEACONSW eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m071;

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

  my $f_EMERSTOP = $self->{features}->getString("EMERSTOP");

  if( !(($f_EMERSTOP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m072;

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

  my $f_LHMIRROR = $self->{features}->getString("LHMIRROR");

  if( !(($f_LHMIRROR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m073;

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

  my $f_RHMIRROR = $self->{features}->getString("RHMIRROR");

  if( !(($f_RHMIRROR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m074;

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

  my $f_STDTOOLS = $self->{features}->getString("STDTOOLS");

  if( !(($f_STDTOOLS eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m075;

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

  my $f_BOOM = $self->{features}->getString("BOOM");
  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");

  if( !(($f_BOOM eq "9")) ) {
    $s_validate = 0;
  }
  if( !(($f_BOOM_OPT eq "CE")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m076;

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

  my $f_SSTSEAT = $self->{features}->getString("SSTSEAT");

  if( !(($f_SSTSEAT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m077;

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

  my $f_PAXSEAT = $self->{features}->getString("PAXSEAT");

  if( !(($f_PAXSEAT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m078;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "P")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m079;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "P")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m080;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");

  if( !(($f_ENGINE eq "PK")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "P")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m081;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");

  if( !(($f_ENGINE eq "PK")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "P")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m082;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_TRANS = $self->{features}->getString("TRANS");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_TRANS eq "P")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m083;

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

  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_THROTTLE eq "DR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m084;

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

  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_THROTTLE eq "RR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m085;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "FOOT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m086;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(((($f_THROTTLE eq "DR")) || (($f_THROTTLE eq "RR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m087;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "PK")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "FOOT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m088;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "PK")) ) {
    $s_validate = 0;
  }
  if( !(((($f_THROTTLE eq "DR")) || (($f_THROTTLE eq "RR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m089;

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

  my $f_ENGINE = $self->{features}->getString("ENGINE");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(($f_THROTTLE eq "FOOT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_m090;

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
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( !(($f_ENGINE eq "IZ")) ) {
    $s_validate = 0;
  }
  if( !(((($f_THROTTLE eq "DR")) || (($f_THROTTLE eq "RR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_NBL::c_o001;

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
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");
  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");

  if( ((($f_BOOM_OPT eq "CAN")) || (($f_BOOM_OPT eq "CC"))) ) {
    $s_run_time = ($s_run_time + 960);
  }
  if( ((((($f_BOOM_OPT eq "CT")) || (($f_BOOM_OPT eq "FC")))) || (($f_BOOM_OPT eq "TC"))) ) {
    $s_run_time = ($s_run_time + 480);
  }
  if( ($f_CABIN eq "Y") ) {
    $s_run_time = ($s_run_time + 840);
  }
  if( ($f_CABDOOR eq "Y") ) {
    $s_run_time = ($s_run_time + 960);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_NBL::c_o002;

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
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");
  my $f_LIFTSPD = $self->{features}->getString("LIFTSPD");
  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_SEATBELT = $self->{features}->getString("SEATBELT");
  my $f_STABLZR = $self->{features}->getString("STABLZR");
  my $f_DECKPROT = $self->{features}->getString("DECKPROT");
  my $f_WOODBUMP = $self->{features}->getString("WOODBUMP");
  my $f_THROTTLE = $self->{features}->getString("THROTTLE");

  if( ($f_BOOM_OPT eq "CAN") ) {
    $s_run_time = ($s_run_time + 480);
  }
  if( ((((($f_BOOM_OPT eq "CC")) || (($f_BOOM_OPT eq "CT")))) || (($f_BOOM_OPT eq "FT"))) ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_BOOM_OPT eq "FC") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_BOOM_OPT eq "TC") ) {
    $s_run_time = ($s_run_time + 90);
  }
  if( ($f_BOOM_OPT eq "TT") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LIFTSPD eq "Y") ) {
    $s_run_time = ($s_run_time + 840);
  }
  if( ($f_CABIN eq "Y") ) {
    $s_run_time = ($s_run_time + 360);
  }
  if( ($f_CABDOOR eq "Y") ) {
    $s_run_time = ($s_run_time + 240);
  }
  if( ($f_SEATBELT eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_STABLZR eq "STAB") ) {
    $s_run_time = ($s_run_time + 360);
  }
  if( ($f_STABLZR eq "MAN") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_DECKPROT eq "ALUM") ) {
    $s_run_time = ($s_run_time + 300);
  }
  if( ($f_DECKPROT eq "TANK") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_WOODBUMP eq "Y") ) {
    $s_run_time = ($s_run_time + 180);
  }
  if( ($f_THROTTLE eq "MAN") ) {
    $s_run_time = ($s_run_time + 120);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_NBL::c_o005;

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
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");
  my $f_STABLZR = $self->{features}->getString("STABLZR");
  my $f_LFCTLBOX = $self->{features}->getString("LFCTLBOX");
  my $f_LRCTLBOX = $self->{features}->getString("LRCTLBOX");
  my $f_WORKLITE = $self->{features}->getString("WORKLITE");
  my $f_PARKBRK = $self->{features}->getString("PARKBRK");

  if( ($f_AIRFAN eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ((($f_CABHEAT eq "DEVICE")) || (($f_CABHEAT eq "ELEC"))) ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_STABLZR eq "STAB") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ((($f_LFCTLBOX eq "EMER")) || (($f_LFCTLBOX eq "FIX"))) ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_LRCTLBOX eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_WORKLITE eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_PARKBRK eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_NBL::c_o011;

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
}

sub parameter_substitution
{
  my $self = shift;
  my $s_run_time = $self->{globals}->getInteger("run_time");

  my $f_BOOM_OPT = $self->{features}->getString("BOOM_OPT");
  my $f_LIFTSPD = $self->{features}->getString("LIFTSPD");
  my $f_CABIN = $self->{features}->getString("CABIN");
  my $f_CABDOOR = $self->{features}->getString("CABDOOR");
  my $f_WINDSCRN = $self->{features}->getString("WINDSCRN");
  my $f_AIRFAN = $self->{features}->getString("AIRFAN");
  my $f_CABHEAT = $self->{features}->getString("CABHEAT");
  my $f_SEATBELT = $self->{features}->getString("SEATBELT");
  my $f_STABLZR = $self->{features}->getString("STABLZR");
  my $f_DECKPROT = $self->{features}->getString("DECKPROT");
  my $f_LFCTLBOX = $self->{features}->getString("LFCTLBOX");
  my $f_LRCTLBOX = $self->{features}->getString("LRCTLBOX");
  my $f_WORKLITE = $self->{features}->getString("WORKLITE");
  my $f_PARKBRK = $self->{features}->getString("PARKBRK");
  my $f_WOODBUMP = $self->{features}->getString("WOODBUMP");

  if( ($f_BOOM_OPT eq "CAN") ) {
    $s_run_time = ($s_run_time + 120);
  }
  if( ((((((((((($f_BOOM_OPT eq "CC")) || (($f_BOOM_OPT eq "CT")))) || (($f_BOOM_OPT eq "FC")))) || (($f_BOOM_OPT eq "FT")))) || (($f_BOOM_OPT eq "TC")))) || (($f_BOOM_OPT eq "TT"))) ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_LIFTSPD eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_CABIN eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_CABDOOR eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_WINDSCRN eq "Y") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_AIRFAN eq "Y") ) {
    $s_run_time = ($s_run_time + 12);
  }
  if( ((($f_CABHEAT eq "DEVICE")) || (($f_CABHEAT eq "ELEC"))) ) {
    $s_run_time = ($s_run_time + 12);
  }
  if( ($f_SEATBELT eq "Y") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_STABLZR eq "STAB") ) {
    $s_run_time = ($s_run_time + 60);
  }
  if( ($f_STABLZR eq "MAN") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_DECKPROT eq "ALUM") ) {
    $s_run_time = ($s_run_time + 30);
  }
  if( ($f_DECKPROT eq "TANK") ) {
    $s_run_time = ($s_run_time + 6);
  }
  if( ((($f_LFCTLBOX eq "EMER")) || (($f_LFCTLBOX eq "FIX"))) ) {
    $s_run_time = ($s_run_time + 12);
  }
  if( ($f_LRCTLBOX eq "Y") ) {
    $s_run_time = ($s_run_time + 12);
  }
  if( ($f_WORKLITE eq "Y") ) {
    $s_run_time = ($s_run_time + 12);
  }
  if( ($f_PARKBRK eq "Y") ) {
    $s_run_time = ($s_run_time + 12);
  }
  if( ($f_WOODBUMP eq "Y") ) {
    $s_run_time = ($s_run_time + 6);
  }

  $self->{globals}->set("run_time", $s_run_time);
}


package configurator::constraints::IC_NBL::c_o008;

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
}

sub parameter_substitution
{
  my $self = shift;
}

1;
