package configurator::constraints::IC_TLD_2d838;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_TLD_2d838",
      "item" => "TLD-838",
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

package configurator::constraints::IC_TLD_2d838::c_f001;

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

  my $f_CHASSIS = $self->{features}->getString("CHASSIS");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_CHASSIS = "";
  if( ($f_CONFIG eq "SUP") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("CHASSIS", $f_CHASSIS);
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


package configurator::constraints::IC_TLD_2d838::c_f002;

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

  my $f_ELEVFRAM = $self->{features}->getString("ELEVFRAM");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 0;
  $s_input = 0;
  $f_ELEVFRAM = "";
  if( ($f_CONFIG eq "SUP") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ELEVFRAM", $f_ELEVFRAM);
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


package configurator::constraints::IC_TLD_2d838::c_f003;

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

  my $f_OILCOOL = $self->{features}->getString("OILCOOL");
  my $f_HYD = $self->{features}->getString("HYD");

  $s_display = 0;
  $s_input = 0;
  $f_OILCOOL = "";
  if( ($f_HYD eq "STD") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("OILCOOL", $f_OILCOOL);
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


package configurator::constraints::IC_TLD_2d838::c_f004;

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

  my $f_CONTRAY = $self->{features}->getString("CONTRAY");
  my $f_OPCONS = $self->{features}->getString("OPCONS");

  $s_display = 0;
  $s_input = 0;
  $f_CONTRAY = "";
  if( ($f_OPCONS eq "FIX") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("CONTRAY", $f_CONTRAY);
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


package configurator::constraints::IC_TLD_2d838::c_f005;

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

  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_CONFIG = $self->{features}->getString("CONFIG");
  my $f_OPCONS = $self->{features}->getString("OPCONS");

  $s_display = 0;
  $s_input = 0;
  $f_RHWALK = "";
  if( ((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_OPCONS eq "FIX"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("RHWALK", $f_RHWALK);
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


package configurator::constraints::IC_TLD_2d838::c_f006;

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

  my $f_BRGLIFT = $self->{features}->getString("BRGLIFT");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  $s_display = 1;
  $s_input = 0;
  $f_BRGLIFT = "Y";
  if( ((((($f_CONFIG eq "")) || (($f_CONFIG eq "STD")))) || (($f_CONFIG eq "WID"))) ) {
    $s_input = 1;
    $f_BRGLIFT = "";
  }

  $self->{features}->set("BRGLIFT", $f_BRGLIFT);
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


package configurator::constraints::IC_TLD_2d838::c_f007;

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

  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_CUSTOPT = $self->{features}->getString("CUSTOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ADDOPT = "";
  if( ($f_CUSTOPT eq "ZZZ") ) {
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


package configurator::constraints::IC_TLD_2d838::c_f008;

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

  my $f_CE828 = $self->{features}->getString("CE828");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_CE828 = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("CE828", $f_CE828);
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


package configurator::constraints::IC_TLD_2d838::c_f009;

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

  my $f_ZSTAB = $self->{features}->getString("ZSTAB");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZSTAB = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZSTAB", $f_ZSTAB);
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


package configurator::constraints::IC_TLD_2d838::c_f010;

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

  my $f_ZLOWF = $self->{features}->getString("ZLOWF");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZLOWF = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZLOWF", $f_ZLOWF);
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


package configurator::constraints::IC_TLD_2d838::c_f011;

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

  my $f_ZREFLECT = $self->{features}->getString("ZREFLECT");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZREFLECT = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZREFLECT", $f_ZREFLECT);
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


package configurator::constraints::IC_TLD_2d838::c_f012;

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

  my $f_ZFIREEXT = $self->{features}->getString("ZFIREEXT");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZFIREEXT = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZFIREEXT", $f_ZFIREEXT);
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


package configurator::constraints::IC_TLD_2d838::c_f013;

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

  my $f_PNEUTIRE = $self->{features}->getString("PNEUTIRE");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_PNEUTIRE = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("PNEUTIRE", $f_PNEUTIRE);
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


package configurator::constraints::IC_TLD_2d838::c_f014;

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

  my $f_PONYA828 = $self->{features}->getString("PONYA828");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_PONYA828 = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("PONYA828", $f_PONYA828);
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


package configurator::constraints::IC_TLD_2d838::c_f015;

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

  my $f_ZAUTOBRK = $self->{features}->getString("ZAUTOBRK");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_PONYA = $self->{features}->getString("PONYA");

  $s_display = 0;
  $s_input = 0;
  $f_ZAUTOBRK = "";
  if( ((($f_ADDOPT eq "YES")) && (($f_PONYA eq "N"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZAUTOBRK", $f_ZAUTOBRK);
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


package configurator::constraints::IC_TLD_2d838::c_f016;

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

  my $f_ZPARKBRK = $self->{features}->getString("ZPARKBRK");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_PONYA = $self->{features}->getString("PONYA");
  my $f_ZAUTOBRK = $self->{features}->getString("ZAUTOBRK");

  $s_display = 0;
  $s_input = 0;
  $f_ZPARKBRK = "";
  if( ((((($f_ADDOPT eq "YES")) && (($f_PONYA eq "N")))) && (($f_ZAUTOBRK eq "N"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZPARKBRK", $f_ZPARKBRK);
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


package configurator::constraints::IC_TLD_2d838::c_f017;

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

  my $f_ZAMB = $self->{features}->getString("ZAMB");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_PONYA = $self->{features}->getString("PONYA");

  $s_display = 0;
  $s_input = 0;
  $f_ZAMB = "";
  if( ((($f_ADDOPT eq "Y")) && (($f_PONYA eq "NO"))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_f018;

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

  my $f_ZMLIGHT = $self->{features}->getString("ZMLIGHT");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_PONYA = $self->{features}->getString("PONYA");

  $s_display = 0;
  $s_input = 0;
  $f_ZMLIGHT = "";
  if( ((($f_ADDOPT eq "Y")) && (($f_PONYA eq "N"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZMLIGHT", $f_ZMLIGHT);
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


package configurator::constraints::IC_TLD_2d838::c_f019;

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

  my $f_ZINTLOCK = $self->{features}->getString("ZINTLOCK");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_PONYA = $self->{features}->getString("PONYA");
  my $f_CE838 = $self->{features}->getString("CE838");

  $s_display = 0;
  $s_input = 0;
  $f_ZINTLOCK = "";
  if( ((((($f_ADDOPT eq "Y")) && (($f_PONYA eq "N")))) && (($f_CE838 eq "NO"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZINTLOCK", $f_ZINTLOCK);
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


package configurator::constraints::IC_TLD_2d838::c_f020;

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

  my $f_ZHORN = $self->{features}->getString("ZHORN");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_PONYA = $self->{features}->getString("PONYA");

  $s_display = 0;
  $s_input = 0;
  $f_ZHORN = "";
  if( ((($f_ADDOPT eq "Y")) && (($f_PONYA eq "N"))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_f021;

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

  my $f_ZTOWBAR = $self->{features}->getString("ZTOWBAR");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZTOWBAR = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZTOWBAR", $f_ZTOWBAR);
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


package configurator::constraints::IC_TLD_2d838::c_f022;

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

  my $f_ZFRTOW = $self->{features}->getString("ZFRTOW");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZFRTOW = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZFRTOW", $f_ZFRTOW);
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


package configurator::constraints::IC_TLD_2d838::c_f023;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f023",
      "origname" => "f023",
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

  my $f_ZRRTOW = $self->{features}->getString("ZRRTOW");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZRRTOW = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZRRTOW", $f_ZRRTOW);
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


package configurator::constraints::IC_TLD_2d838::c_f024;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f024",
      "origname" => "f024",
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

  my $f_ZLOWOIL = $self->{features}->getString("ZLOWOIL");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZLOWOIL = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZLOWOIL", $f_ZLOWOIL);
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


package configurator::constraints::IC_TLD_2d838::c_f025;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f025",
      "origname" => "f025",
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

  my $f_ZLOWPRES = $self->{features}->getString("ZLOWPRES");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZLOWPRES = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZLOWPRES", $f_ZLOWPRES);
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


package configurator::constraints::IC_TLD_2d838::c_f026;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f026",
      "origname" => "f026",
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

  my $f_ZILGAUGE = $self->{features}->getString("ZILGAUGE");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZILGAUGE = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZILGAUGE", $f_ZILGAUGE);
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


package configurator::constraints::IC_TLD_2d838::c_f027;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f027",
      "origname" => "f027",
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

  my $f_ZHPUMP = $self->{features}->getString("ZHPUMP");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZHPUMP = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZHPUMP", $f_ZHPUMP);
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


package configurator::constraints::IC_TLD_2d838::c_f028;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f028",
      "origname" => "f028",
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

  my $f_ZBRHORN = $self->{features}->getString("ZBRHORN");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZBRHORN = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZBRHORN", $f_ZBRHORN);
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


package configurator::constraints::IC_TLD_2d838::c_f029;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f029",
      "origname" => "f029",
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

  my $f_ZBATQCK = $self->{features}->getString("ZBATQCK");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZBATQCK = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZBATQCK", $f_ZBATQCK);
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


package configurator::constraints::IC_TLD_2d838::c_f030;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f030",
      "origname" => "f030",
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

  my $f_ZEMSTOP = $self->{features}->getString("ZEMSTOP");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZEMSTOP = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZEMSTOP", $f_ZEMSTOP);
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


package configurator::constraints::IC_TLD_2d838::c_f031;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f031",
      "origname" => "f031",
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


package configurator::constraints::IC_TLD_2d838::c_f032;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f032",
      "origname" => "f032",
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

  my $f_ZAUTOSHU = $self->{features}->getString("ZAUTOSHU");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZAUTOSHU = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZAUTOSHU", $f_ZAUTOSHU);
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


package configurator::constraints::IC_TLD_2d838::c_f033;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f033",
      "origname" => "f033",
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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_Z110COLD = "";
  if( ((($f_ADDOPT eq "Y")) && (((($f_ENGINE eq "DU4")) || (($f_ENGINE eq "CAT"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_f034;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f034",
      "origname" => "f034",
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
  my $f_ENGINE = $self->{features}->getString("ENGINE");

  $s_display = 0;
  $s_input = 0;
  $f_Z220COLD = "";
  if( ((($f_ADDOPT eq "Y")) && (((($f_ENGINE eq "DU4")) || (($f_ENGINE eq "CAT"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_f035;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f035",
      "origname" => "f035",
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


package configurator::constraints::IC_TLD_2d838::c_f036;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f036",
      "origname" => "f036",
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


package configurator::constraints::IC_TLD_2d838::c_f037;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f037",
      "origname" => "f037",
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


package configurator::constraints::IC_TLD_2d838::c_f038;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f038",
      "origname" => "f038",
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


package configurator::constraints::IC_TLD_2d838::c_f039;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f039",
      "origname" => "f039",
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


package configurator::constraints::IC_TLD_2d838::c_f040;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f040",
      "origname" => "f040",
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


package configurator::constraints::IC_TLD_2d838::c_f041;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f041",
      "origname" => "f041",
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

  $s_display = 0;
  $s_input = 0;
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

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


package configurator::constraints::IC_TLD_2d838::c_f042;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f042",
      "origname" => "f042",
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

  my $f_ZBUMPER = $self->{features}->getString("ZBUMPER");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZBUMPER = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZBUMPER", $f_ZBUMPER);
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


package configurator::constraints::IC_TLD_2d838::c_f043;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f043",
      "origname" => "f043",
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

  my $f_ZRRFLLGT = $self->{features}->getString("ZRRFLLGT");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZRRFLLGT = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZRRFLLGT", $f_ZRRFLLGT);
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


package configurator::constraints::IC_TLD_2d838::c_f044;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f044",
      "origname" => "f044",
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

  my $f_ZEXTGUID = $self->{features}->getString("ZEXTGUID");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZEXTGUID = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZEXTGUID", $f_ZEXTGUID);
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


package configurator::constraints::IC_TLD_2d838::c_f045;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f045",
      "origname" => "f045",
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

  my $f_ZBEACUND = $self->{features}->getString("ZBEACUND");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZBEACUND = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZBEACUND", $f_ZBEACUND);
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


package configurator::constraints::IC_TLD_2d838::c_f046;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f046",
      "origname" => "f046",
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

  my $f_ZBEACELE = $self->{features}->getString("ZBEACELE");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZBEACELE = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZBEACELE", $f_ZBEACELE);
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


package configurator::constraints::IC_TLD_2d838::c_f047;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f047",
      "origname" => "f047",
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


package configurator::constraints::IC_TLD_2d838::c_f048;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f048",
      "origname" => "f048",
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


package configurator::constraints::IC_TLD_2d838::c_f049;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f049",
      "origname" => "f049",
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


package configurator::constraints::IC_TLD_2d838::c_f050;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f050",
      "origname" => "f050",
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

  my $f_ZELEVCOV = $self->{features}->getString("ZELEVCOV");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");
  my $f_PONYA = $self->{features}->getString("PONYA");
  my $f_CE838 = $self->{features}->getString("CE838");

  $s_display = 0;
  $s_input = 0;
  $f_ZELEVCOV = "";
  if( ((((($f_ADDOPT eq "Y")) && (($f_PONYA eq "NO")))) && (($f_CE838 eq "NO"))) ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZELEVCOV", $f_ZELEVCOV);
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


package configurator::constraints::IC_TLD_2d838::c_f051;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f051",
      "origname" => "f051",
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

  my $f_ZSENSGUI = $self->{features}->getString("ZSENSGUI");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZSENSGUI = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZSENSGUI", $f_ZSENSGUI);
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


package configurator::constraints::IC_TLD_2d838::c_f052;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f052",
      "origname" => "f052",
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

  my $f_ZSIDINTL = $self->{features}->getString("ZSIDINTL");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZSIDINTL = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZSIDINTL", $f_ZSIDINTL);
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


package configurator::constraints::IC_TLD_2d838::c_f053;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f053",
      "origname" => "f053",
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


package configurator::constraints::IC_TLD_2d838::c_f054;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_f054",
      "origname" => "f054",
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

  my $f_ZLHCOLLA = $self->{features}->getString("ZLHCOLLA");
  my $f_ADDOPT = $self->{features}->getString("ADDOPT");

  $s_display = 0;
  $s_input = 0;
  $f_ZLHCOLLA = "";
  if( ($f_ADDOPT eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("ZLHCOLLA", $f_ZLHCOLLA);
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


package configurator::constraints::IC_TLD_2d838::c_o001;

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


package configurator::constraints::IC_TLD_2d838::c_o002;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o003;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o004;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o005;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o006;

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


package configurator::constraints::IC_TLD_2d838::c_o007;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o008;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o009;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o010;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o011;

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


package configurator::constraints::IC_TLD_2d838::c_o012;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o013;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o014;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o015;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o016;

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


package configurator::constraints::IC_TLD_2d838::c_o017;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o018;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o019;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o020;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o021;

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


package configurator::constraints::IC_TLD_2d838::c_o022;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o023;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o024;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o025;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o026;

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


package configurator::constraints::IC_TLD_2d838::c_o027;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o028;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o029;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o030;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o031;

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


package configurator::constraints::IC_TLD_2d838::c_o032;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o033;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o034;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o035;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o036;

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


package configurator::constraints::IC_TLD_2d838::c_o037;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o038;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o039;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o040;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o041;

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


package configurator::constraints::IC_TLD_2d838::c_o042;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o043;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o044;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o045;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o046;

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


package configurator::constraints::IC_TLD_2d838::c_o047;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o048;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o049;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o050;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o051;

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


package configurator::constraints::IC_TLD_2d838::c_o052;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o053;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o054;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o055;

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

  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o056;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o056",
      "origname" => "o056",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o057;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o057",
      "origname" => "o057",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o058;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o058",
      "origname" => "o058",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o059;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o059",
      "origname" => "o059",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o060;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o060",
      "origname" => "o060",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o061;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o061",
      "origname" => "o061",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o062;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o062",
      "origname" => "o062",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o063;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o063",
      "origname" => "o063",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o064;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o064",
      "origname" => "o064",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o065;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o065",
      "origname" => "o065",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o066;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o066",
      "origname" => "o066",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o067;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o067",
      "origname" => "o067",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o068;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o068",
      "origname" => "o068",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o069;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o069",
      "origname" => "o069",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o070;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o070",
      "origname" => "o070",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o071;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o071",
      "origname" => "o071",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o072;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o072",
      "origname" => "o072",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o073;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o073",
      "origname" => "o073",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o074;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o074",
      "origname" => "o074",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o075;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o075",
      "origname" => "o075",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o076;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o076",
      "origname" => "o076",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o077;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o077",
      "origname" => "o077",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o078;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o078",
      "origname" => "o078",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o079;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o079",
      "origname" => "o079",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o080;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o080",
      "origname" => "o080",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o081;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o081",
      "origname" => "o081",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o082;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o082",
      "origname" => "o082",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o083;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o083",
      "origname" => "o083",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o084;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o084",
      "origname" => "o084",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o085;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o085",
      "origname" => "o085",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o086;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o086",
      "origname" => "o086",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o087;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o087",
      "origname" => "o087",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o088;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o088",
      "origname" => "o088",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o089;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o089",
      "origname" => "o089",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o090;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o090",
      "origname" => "o090",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o091;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o091",
      "origname" => "o091",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o092;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o092",
      "origname" => "o092",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o093;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o093",
      "origname" => "o093",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o094;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o094",
      "origname" => "o094",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o095;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o095",
      "origname" => "o095",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o096;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o096",
      "origname" => "o096",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o097;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o097",
      "origname" => "o097",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o098;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o098",
      "origname" => "o098",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o099;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o099",
      "origname" => "o099",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o100;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o100",
      "origname" => "o100",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o101;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o101",
      "origname" => "o101",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o102;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o102",
      "origname" => "o102",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o103;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o103",
      "origname" => "o103",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o104;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o104",
      "origname" => "o104",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o105;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o105",
      "origname" => "o105",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o106;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o106",
      "origname" => "o106",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o107;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o107",
      "origname" => "o107",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o108;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o108",
      "origname" => "o108",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o109;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o109",
      "origname" => "o109",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o110;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o110",
      "origname" => "o110",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o111;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o111",
      "origname" => "o111",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o112;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o112",
      "origname" => "o112",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o113;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o113",
      "origname" => "o113",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o114;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o114",
      "origname" => "o114",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o115;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o115",
      "origname" => "o115",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o116;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o116",
      "origname" => "o116",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o117;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o117",
      "origname" => "o117",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o118;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o118",
      "origname" => "o118",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o119;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o119",
      "origname" => "o119",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o120;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o120",
      "origname" => "o120",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o121;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o121",
      "origname" => "o121",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o122;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o122",
      "origname" => "o122",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o123;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o123",
      "origname" => "o123",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o124;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o124",
      "origname" => "o124",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o125;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o125",
      "origname" => "o125",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o126;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o126",
      "origname" => "o126",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o127;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o127",
      "origname" => "o127",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o128;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o128",
      "origname" => "o128",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o129;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o129",
      "origname" => "o129",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o130;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o130",
      "origname" => "o130",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o131;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o131",
      "origname" => "o131",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o132;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o132",
      "origname" => "o132",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o133;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o133",
      "origname" => "o133",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o134;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o134",
      "origname" => "o134",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o135;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o135",
      "origname" => "o135",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o136;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o136",
      "origname" => "o136",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o137;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o137",
      "origname" => "o137",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o138;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o138",
      "origname" => "o138",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o139;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o139",
      "origname" => "o139",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o140;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o140",
      "origname" => "o140",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o141;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o141",
      "origname" => "o141",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o142;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o142",
      "origname" => "o142",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o143;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o143",
      "origname" => "o143",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o144;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o144",
      "origname" => "o144",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o145;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o145",
      "origname" => "o145",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o146;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o146",
      "origname" => "o146",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o147;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o147",
      "origname" => "o147",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o148;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o148",
      "origname" => "o148",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o149;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o149",
      "origname" => "o149",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o150;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o150",
      "origname" => "o150",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o151;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o151",
      "origname" => "o151",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o152;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o152",
      "origname" => "o152",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o153;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o153",
      "origname" => "o153",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o154;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o154",
      "origname" => "o154",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o155;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o155",
      "origname" => "o155",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o156;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o156",
      "origname" => "o156",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o157;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o157",
      "origname" => "o157",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o158;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o158",
      "origname" => "o158",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o159;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o159",
      "origname" => "o159",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o160;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o160",
      "origname" => "o160",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o161;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o161",
      "origname" => "o161",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o162;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o162",
      "origname" => "o162",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o163;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o163",
      "origname" => "o163",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o164;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o164",
      "origname" => "o164",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o165;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o165",
      "origname" => "o165",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o166;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o166",
      "origname" => "o166",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o167;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o167",
      "origname" => "o167",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o168;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o168",
      "origname" => "o168",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o169;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o169",
      "origname" => "o169",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o170;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o170",
      "origname" => "o170",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o171;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o171",
      "origname" => "o171",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o172;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o172",
      "origname" => "o172",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o173;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o173",
      "origname" => "o173",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o174;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o174",
      "origname" => "o174",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o175;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o175",
      "origname" => "o175",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o176;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o176",
      "origname" => "o176",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o177;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o177",
      "origname" => "o177",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o178;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o178",
      "origname" => "o178",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o179;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o179",
      "origname" => "o179",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o180;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o180",
      "origname" => "o180",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o181;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o181",
      "origname" => "o181",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o182;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o182",
      "origname" => "o182",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o183;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o183",
      "origname" => "o183",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o184;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o184",
      "origname" => "o184",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o185;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o185",
      "origname" => "o185",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o186;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o186",
      "origname" => "o186",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o187;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o187",
      "origname" => "o187",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o188;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o188",
      "origname" => "o188",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o189;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o189",
      "origname" => "o189",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o190;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o190",
      "origname" => "o190",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o191;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o191",
      "origname" => "o191",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o192;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o192",
      "origname" => "o192",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o193;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o193",
      "origname" => "o193",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o194;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o194",
      "origname" => "o194",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o195;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o195",
      "origname" => "o195",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o196;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o196",
      "origname" => "o196",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o197;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o197",
      "origname" => "o197",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o198;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o198",
      "origname" => "o198",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o199;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o199",
      "origname" => "o199",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o200;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o200",
      "origname" => "o200",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o201;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o201",
      "origname" => "o201",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o202;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o202",
      "origname" => "o202",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o203;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o203",
      "origname" => "o203",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o204;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o204",
      "origname" => "o204",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o205;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o205",
      "origname" => "o205",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o206;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o206",
      "origname" => "o206",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o207;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o207",
      "origname" => "o207",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o208;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o208",
      "origname" => "o208",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o209;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o209",
      "origname" => "o209",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o210;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o210",
      "origname" => "o210",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o211;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o211",
      "origname" => "o211",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o212;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o212",
      "origname" => "o212",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o213;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o213",
      "origname" => "o213",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o214;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o214",
      "origname" => "o214",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o215;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o215",
      "origname" => "o215",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o216;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o216",
      "origname" => "o216",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o217;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o217",
      "origname" => "o217",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o218;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o218",
      "origname" => "o218",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o219;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o219",
      "origname" => "o219",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o220;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o220",
      "origname" => "o220",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o221;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o221",
      "origname" => "o221",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o222;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o222",
      "origname" => "o222",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o223;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o223",
      "origname" => "o223",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o224;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o224",
      "origname" => "o224",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o225;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o225",
      "origname" => "o225",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o226;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o226",
      "origname" => "o226",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o227;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o227",
      "origname" => "o227",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o228;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o228",
      "origname" => "o228",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o229;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o229",
      "origname" => "o229",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o230;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o230",
      "origname" => "o230",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o231;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o231",
      "origname" => "o231",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o232;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o232",
      "origname" => "o232",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o233;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o233",
      "origname" => "o233",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o234;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o234",
      "origname" => "o234",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o235;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o235",
      "origname" => "o235",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o236;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o236",
      "origname" => "o236",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o237;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o237",
      "origname" => "o237",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o238;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o238",
      "origname" => "o238",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o239;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o239",
      "origname" => "o239",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o240;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o240",
      "origname" => "o240",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o241;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o241",
      "origname" => "o241",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o242;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o242",
      "origname" => "o242",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o243;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o243",
      "origname" => "o243",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o244;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o244",
      "origname" => "o244",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o245;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o245",
      "origname" => "o245",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o246;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o246",
      "origname" => "o246",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o247;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o247",
      "origname" => "o247",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o248;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o248",
      "origname" => "o248",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o249;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o249",
      "origname" => "o249",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o250;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o250",
      "origname" => "o250",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o251;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o251",
      "origname" => "o251",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o252;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o252",
      "origname" => "o252",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o253;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o253",
      "origname" => "o253",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o254;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o254",
      "origname" => "o254",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o255;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o255",
      "origname" => "o255",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o256;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o256",
      "origname" => "o256",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o257;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o257",
      "origname" => "o257",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o258;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o258",
      "origname" => "o258",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o259;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o259",
      "origname" => "o259",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o260;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o260",
      "origname" => "o260",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o261;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o261",
      "origname" => "o261",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o262;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o262",
      "origname" => "o262",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o263;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o263",
      "origname" => "o263",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o264;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o264",
      "origname" => "o264",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o265;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o265",
      "origname" => "o265",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
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


package configurator::constraints::IC_TLD_2d838::c_o266;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o266",
      "origname" => "o266",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o267;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o267",
      "origname" => "o267",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o268;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o268",
      "origname" => "o268",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o269;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o269",
      "origname" => "o269",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o270;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o270",
      "origname" => "o270",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o271;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o271",
      "origname" => "o271",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o272;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o272",
      "origname" => "o272",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o273;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o273",
      "origname" => "o273",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o274;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o274",
      "origname" => "o274",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o275;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o275",
      "origname" => "o275",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o276;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o276",
      "origname" => "o276",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o277;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o277",
      "origname" => "o277",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o278;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o278",
      "origname" => "o278",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o279;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o279",
      "origname" => "o279",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o280;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o280",
      "origname" => "o280",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o281;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o281",
      "origname" => "o281",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o282;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o282",
      "origname" => "o282",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o283;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o283",
      "origname" => "o283",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o284;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o284",
      "origname" => "o284",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o285;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o285",
      "origname" => "o285",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o286;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o286",
      "origname" => "o286",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o287;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o287",
      "origname" => "o287",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o288;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o288",
      "origname" => "o288",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o289;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o289",
      "origname" => "o289",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o290;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o290",
      "origname" => "o290",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o291;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o291",
      "origname" => "o291",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o292;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o292",
      "origname" => "o292",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o293;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o293",
      "origname" => "o293",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o294;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o294",
      "origname" => "o294",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o295;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o295",
      "origname" => "o295",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o296;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o296",
      "origname" => "o296",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o297;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o297",
      "origname" => "o297",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o298;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o298",
      "origname" => "o298",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o299;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o299",
      "origname" => "o299",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o300;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o300",
      "origname" => "o300",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o301;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o301",
      "origname" => "o301",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o302;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o302",
      "origname" => "o302",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o303;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o303",
      "origname" => "o303",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o304;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o304",
      "origname" => "o304",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o305;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o305",
      "origname" => "o305",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o306;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o306",
      "origname" => "o306",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o307;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o307",
      "origname" => "o307",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o308;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o308",
      "origname" => "o308",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o309;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o309",
      "origname" => "o309",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o310;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o310",
      "origname" => "o310",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o311;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o311",
      "origname" => "o311",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o312;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o312",
      "origname" => "o312",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o313;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o313",
      "origname" => "o313",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o314;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o314",
      "origname" => "o314",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o315;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o315",
      "origname" => "o315",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o316;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o316",
      "origname" => "o316",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o317;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o317",
      "origname" => "o317",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o318;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o318",
      "origname" => "o318",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o319;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o319",
      "origname" => "o319",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o320;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o320",
      "origname" => "o320",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o321;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o321",
      "origname" => "o321",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o322;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o322",
      "origname" => "o322",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o323;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o323",
      "origname" => "o323",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o324;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o324",
      "origname" => "o324",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o325;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o325",
      "origname" => "o325",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o326;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o326",
      "origname" => "o326",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o327;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o327",
      "origname" => "o327",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o328;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o328",
      "origname" => "o328",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o329;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o329",
      "origname" => "o329",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o330;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o330",
      "origname" => "o330",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o331;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o331",
      "origname" => "o331",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o332;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o332",
      "origname" => "o332",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o333;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o333",
      "origname" => "o333",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o334;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o334",
      "origname" => "o334",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o335;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o335",
      "origname" => "o335",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o336;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o336",
      "origname" => "o336",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o337;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o337",
      "origname" => "o337",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o338;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o338",
      "origname" => "o338",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o339;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o339",
      "origname" => "o339",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o340;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o340",
      "origname" => "o340",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o341;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o341",
      "origname" => "o341",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o342;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o342",
      "origname" => "o342",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o343;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o343",
      "origname" => "o343",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o344;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o344",
      "origname" => "o344",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o345;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o345",
      "origname" => "o345",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o346;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o346",
      "origname" => "o346",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o347;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o347",
      "origname" => "o347",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o348;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o348",
      "origname" => "o348",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o349;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o349",
      "origname" => "o349",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o350;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o350",
      "origname" => "o350",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o351;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o351",
      "origname" => "o351",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o352;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o352",
      "origname" => "o352",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o353;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o353",
      "origname" => "o353",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o354;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o354",
      "origname" => "o354",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o355;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o355",
      "origname" => "o355",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o356;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o356",
      "origname" => "o356",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o357;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o357",
      "origname" => "o357",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o358;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o358",
      "origname" => "o358",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o359;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o359",
      "origname" => "o359",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o360;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o360",
      "origname" => "o360",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o361;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o361",
      "origname" => "o361",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o362;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o362",
      "origname" => "o362",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o363;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o363",
      "origname" => "o363",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o364;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o364",
      "origname" => "o364",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o365;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o365",
      "origname" => "o365",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o366;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o366",
      "origname" => "o366",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o367;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o367",
      "origname" => "o367",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o368;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o368",
      "origname" => "o368",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o369;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o369",
      "origname" => "o369",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o370;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o370",
      "origname" => "o370",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o371;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o371",
      "origname" => "o371",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o372;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o372",
      "origname" => "o372",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o373;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o373",
      "origname" => "o373",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o374;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o374",
      "origname" => "o374",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o375;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o375",
      "origname" => "o375",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o376;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o376",
      "origname" => "o376",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o377;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o377",
      "origname" => "o377",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o378;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o378",
      "origname" => "o378",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o379;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o379",
      "origname" => "o379",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o380;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o380",
      "origname" => "o380",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o381;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o381",
      "origname" => "o381",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o382;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o382",
      "origname" => "o382",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o383;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o383",
      "origname" => "o383",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o384;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o384",
      "origname" => "o384",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o385;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o385",
      "origname" => "o385",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o386;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o386",
      "origname" => "o386",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o387;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o387",
      "origname" => "o387",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o388;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o388",
      "origname" => "o388",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o389;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o389",
      "origname" => "o389",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o390;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o390",
      "origname" => "o390",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o391;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o391",
      "origname" => "o391",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o392;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o392",
      "origname" => "o392",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o393;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o393",
      "origname" => "o393",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o394;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o394",
      "origname" => "o394",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o395;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o395",
      "origname" => "o395",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o396;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o396",
      "origname" => "o396",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_o397;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o397",
      "origname" => "o397",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o398;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o398",
      "origname" => "o398",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o399;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o399",
      "origname" => "o399",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "UNI")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_o400;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_o400",
      "origname" => "o400",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0001;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0001",
      "origname" => "m0001",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0002;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0002",
      "origname" => "m0002",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0003;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0003",
      "origname" => "m0003",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");
  my $f_HYD = $self->{features}->getString("HYD");
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( ((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_OILCOOL eq "Y")))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_HYD eq "TURBO")))))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_PALLSTOP eq "FOR"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0004;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0004",
      "origname" => "m0004",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0005;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0005",
      "origname" => "m0005",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");
  my $f_HYD = $self->{features}->getString("HYD");
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( ((((((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) && (($f_OILCOOL eq "Y")))) || (((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) && (($f_HYD eq "TURBO")))))) || (((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) && (($f_PALLSTOP eq "FOR"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0006;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0006",
      "origname" => "m0006",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0007;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0007",
      "origname" => "m0007",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");
  my $f_CHASSIS = $self->{features}->getString("CHASSIS");
  my $f_HYD = $self->{features}->getString("HYD");
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( ((((((((($f_CONFIG eq "SUP")) && (($f_OILCOOL eq "Y")))) && (($f_CHASSIS eq "STD")))) || (((((($f_CONFIG eq "SUP")) && (($f_HYD eq "TURBO")))) && (($f_CHASSIS eq "STD")))))) || (((((($f_CONFIG eq "SUP")) && (($f_PALLSTOP eq "FOR")))) && (($f_CHASSIS eq "STD"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0008;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0008",
      "origname" => "m0008",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_CHASSIS = $self->{features}->getString("CHASSIS");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_CHASSIS eq "HAS")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0009;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0009",
      "origname" => "m0009",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( ((((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (((($f_BRGTRAY eq "PWR")) || (($f_BRGTRAY eq "PWRMDW"))))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0010;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0010",
      "origname" => "m0010",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0011;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0011",
      "origname" => "m0011",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PUMP = $self->{features}->getString("PUMP");
  my $f_HYD = $self->{features}->getString("HYD");

  if( ((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM"))) ) {
    if( ((((($f_PUMP eq "KAWA80")) && (($f_HYD eq "STD")))) || (($f_HYD eq "TURBO"))) ) {
      $s_validate = 1;
    }
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


package configurator::constraints::IC_TLD_2d838::c_m0012;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0012",
      "origname" => "m0012",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PUMP = $self->{features}->getString("PUMP");
  my $f_HYD = $self->{features}->getString("HYD");

  if( ((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP"))) ) {
    if( ((((($f_PUMP eq "KAWA80")) && (($f_HYD eq "STD")))) || (($f_HYD eq "TURBO"))) ) {
      $s_validate = 1;
    }
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


package configurator::constraints::IC_TLD_2d838::c_m0013;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0013",
      "origname" => "m0013",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OILCOOL eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0014;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0014",
      "origname" => "m0014",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OILCOOL eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0015;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0015",
      "origname" => "m0015",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");

  if( !(((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OILCOOL eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0016;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0016",
      "origname" => "m0016",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");

  if( !(((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OILCOOL eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0017;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0017",
      "origname" => "m0017",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RRWHEEL = $self->{features}->getString("RRWHEEL");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) ) {
    $s_validate = 0;
  }
  if( !(($f_RRWHEEL eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0018;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0018",
      "origname" => "m0018",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RRWHEEL = $self->{features}->getString("RRWHEEL");

  if( !(((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_RRWHEEL eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0019;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0019",
      "origname" => "m0019",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RRWHEEL = $self->{features}->getString("RRWHEEL");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) ) {
    $s_validate = 0;
  }
  if( !(($f_RRWHEEL eq "ADJUST")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0020;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0020",
      "origname" => "m0020",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RRWHEEL = $self->{features}->getString("RRWHEEL");

  if( !(((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_RRWHEEL eq "ADJUST")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0021;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0021",
      "origname" => "m0021",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0022;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0022",
      "origname" => "m0022",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0023;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0023",
      "origname" => "m0023",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0024;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0024",
      "origname" => "m0024",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ENGINE eq "DU4")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0025;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0025",
      "origname" => "m0025",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0026;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0026",
      "origname" => "m0026",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_HYD = $self->{features}->getString("HYD");
  my $f_PUMP = $self->{features}->getString("PUMP");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_HYD eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_PUMP eq "PARK65")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0027;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0027",
      "origname" => "m0027",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_HYD = $self->{features}->getString("HYD");
  my $f_PUMP = $self->{features}->getString("PUMP");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_HYD eq "TURBO")) ) {
    $s_validate = 0;
  }
  if( !(($f_PUMP eq "PARK100")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0028;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0028",
      "origname" => "m0028",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PUMP = $self->{features}->getString("PUMP");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_PUMP eq "KAWA80")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0029;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0029",
      "origname" => "m0029",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0030;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0030",
      "origname" => "m0030",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0031;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0031",
      "origname" => "m0031",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0032;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0032",
      "origname" => "m0032",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "32")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0033;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0033",
      "origname" => "m0033",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "32SS")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0034;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0034",
      "origname" => "m0034",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0035;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0035",
      "origname" => "m0035",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "60SS")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0036;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0036",
      "origname" => "m0036",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_FUELTK eq "32SSL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0037;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0037",
      "origname" => "m0037",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(((((($f_FUELTK eq "32")) || (($f_FUELTK eq "32SS")))) || (($f_FUELTK eq "32SSL")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0038;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0038",
      "origname" => "m0038",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(((((($f_FUELTK eq "32")) || (($f_FUELTK eq "32SS")))) || (($f_FUELTK eq "32SSL")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0039;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0039",
      "origname" => "m0039",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_FUELTK eq "60")) || (($f_FUELTK eq "60SS")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0040;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0040",
      "origname" => "m0040",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_FUELTK eq "60")) || (($f_FUELTK eq "60SS")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0041;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0041",
      "origname" => "m0041",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_GAUGE = $self->{features}->getString("GAUGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_GAUGE eq "STD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0042;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0042",
      "origname" => "m0042",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_GAUGE = $self->{features}->getString("GAUGE");

  if( ((((((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_FUELTK eq "60")))) && (($f_GAUGE eq "QUAD")))) || (((((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_FUELTK eq "60SS")))) && (($f_GAUGE eq "QUAD"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0043;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0043",
      "origname" => "m0043",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_GAUGE = $self->{features}->getString("GAUGE");

  if( ((((((((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_FUELTK eq "32")))) && (($f_GAUGE eq "QUAD")))) || (((((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_FUELTK eq "32SS")))) && (($f_GAUGE eq "QUAD")))))) || (((((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_FUELTK eq "32SSL")))) && (($f_GAUGE eq "QUAD"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0044;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0044",
      "origname" => "m0044",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");
  my $f_HYD = $self->{features}->getString("HYD");

  if( ((((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_OILCOOL eq "Y")))) || (((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_HYD eq "TURBO"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0045;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0045",
      "origname" => "m0045",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0046;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0046",
      "origname" => "m0046",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0047;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0047",
      "origname" => "m0047",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0048;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0048",
      "origname" => "m0048",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0049;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0049",
      "origname" => "m0049",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0050;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0050",
      "origname" => "m0050",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "P3")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0051;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0051",
      "origname" => "m0051",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "P2")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0052;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0052",
      "origname" => "m0052",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "P1")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0053;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0053",
      "origname" => "m0053",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0054;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0054",
      "origname" => "m0054",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0055;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0055",
      "origname" => "m0055",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0056;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0056",
      "origname" => "m0056",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0057;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0057",
      "origname" => "m0057",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0058;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0058",
      "origname" => "m0058",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0059;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0059",
      "origname" => "m0059",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( ((((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_BRGTRAY eq "MANMDW"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0060;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0060",
      "origname" => "m0060",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( ((((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_BRGTRAY eq "MAN"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0061;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0061",
      "origname" => "m0061",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( ((((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_BRGTRAY eq "PWRMDW"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0062;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0062",
      "origname" => "m0062",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( ((((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) && (($f_BRGTRAY eq "PWRMDW"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0063;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0063",
      "origname" => "m0063",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0064;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0064",
      "origname" => "m0064",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0065;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0065",
      "origname" => "m0065",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "MAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0066;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0066",
      "origname" => "m0066",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "MAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0067;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0067",
      "origname" => "m0067",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRGTRAY eq "POW")) || (($f_BRGTRAY eq "RLLR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0068;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0068",
      "origname" => "m0068",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRGTRAY eq "POW")) || (($f_BRGTRAY eq "RLLR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0069;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0069",
      "origname" => "m0069",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0070;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0070",
      "origname" => "m0070",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "SS")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0071;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0071",
      "origname" => "m0071",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "MAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0072;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0072",
      "origname" => "m0072",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "MAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0073;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0073",
      "origname" => "m0073",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRGTRAY eq "POW")) || (($f_BRGTRAY eq "RLLR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0074;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0074",
      "origname" => "m0074",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRGTRAY eq "POW")) || (($f_BRGTRAY eq "RLLR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0075;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0075",
      "origname" => "m0075",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0076;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0076",
      "origname" => "m0076",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0077;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0077",
      "origname" => "m0077",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "MAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0078;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0078",
      "origname" => "m0078",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "MAN")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0079;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0079",
      "origname" => "m0079",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRGTRAY eq "POW")) || (($f_BRGTRAY eq "RLLR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0080;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0080",
      "origname" => "m0080",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRGTRAY eq "POW")) || (($f_BRGTRAY eq "RLLR")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0081;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0081",
      "origname" => "m0081",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0082;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0082",
      "origname" => "m0082",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "EL")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0083;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0083",
      "origname" => "m0083",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0084;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0084",
      "origname" => "m0084",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0085;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0085",
      "origname" => "m0085",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_CONTRAY eq "NO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0086;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0086",
      "origname" => "m0086",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_CONTRAY eq "MAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0087;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0087",
      "origname" => "m0087",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_CONTRAY eq "POW")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0088;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0088",
      "origname" => "m0088",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0089;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0089",
      "origname" => "m0089",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0090;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0090",
      "origname" => "m0090",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(((((($f_ELEV eq "EL")) || (($f_ELEV eq "SR")))) || (($f_ELEV eq "DR")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRIDGE eq "EL")) || (($f_BRIDGE eq "SS")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0091;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0091",
      "origname" => "m0091",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "DLX")) ) {
    $s_validate = 0;
  }
  if( !(((((($f_ELEV eq "EL")) || (($f_ELEV eq "SR")))) || (($f_ELEV eq "DR")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRIDGE eq "EL")) || (($f_BRIDGE eq "SS")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0092;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0092",
      "origname" => "m0092",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_BRGTRAY eq "POW")) || (($f_BRGTRAY eq "RLLR")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0093;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0093",
      "origname" => "m0093",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0094;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0094",
      "origname" => "m0094",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTILT = $self->{features}->getString("BRGTILT");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTILT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0095;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0095",
      "origname" => "m0095",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( ((((((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_OPCONS eq "POW")))) || (((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_RHWALK eq "POW")))))) || (((((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) && (($f_CONTRAY eq "POW"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0096;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0096",
      "origname" => "m0096",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ELEVLIFT = $self->{features}->getString("ELEVLIFT");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEVLIFT eq "N")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0097;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0097",
      "origname" => "m0097",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ELEVLIFT = $self->{features}->getString("ELEVLIFT");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEVLIFT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0098;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0098",
      "origname" => "m0098",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGLIFT = $self->{features}->getString("BRGLIFT");

  if( ((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) && (($f_BRGLIFT eq "Y")))) || (((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "SUP"))))) ) {
    $s_validate = 0;
  }
  else {
    $s_validate = 1;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0099;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0099",
      "origname" => "m0099",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGLIFT = $self->{features}->getString("BRGLIFT");

  if( ((((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) || (((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) && (($f_BRGLIFT eq "Y"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0100;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0100",
      "origname" => "m0100",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "COM")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0101;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0101",
      "origname" => "m0101",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0102;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0102",
      "origname" => "m0102",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(((((($f_BRIDGE eq "P1")) || (($f_BRIDGE eq "P2")))) || (($f_BRIDGE eq "P3")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0103;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0103",
      "origname" => "m0103",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "DLX")) ) {
    $s_validate = 0;
  }
  if( !(($f_BRIDGE eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0104;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0104",
      "origname" => "m0104",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_BRIDGE = $self->{features}->getString("BRIDGE");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "DLX")) ) {
    $s_validate = 0;
  }
  if( !(((((($f_BRIDGE eq "P1")) || (($f_BRIDGE eq "P2")))) || (($f_BRIDGE eq "P3")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0105;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0105",
      "origname" => "m0105",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0106;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0106",
      "origname" => "m0106",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ELEV eq "P4")) || (($f_ELEV eq "P6")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0107;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0107",
      "origname" => "m0107",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P8")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0108;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0108",
      "origname" => "m0108",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "DLX")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0109;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0109",
      "origname" => "m0109",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "DLX")) ) {
    $s_validate = 0;
  }
  if( !(((($f_ELEV eq "P4")) || (($f_ELEV eq "P6")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0110;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0110",
      "origname" => "m0110",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_JOYSTK = $self->{features}->getString("JOYSTK");
  my $f_ELEV = $self->{features}->getString("ELEV");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_JOYSTK eq "DLX")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P8")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0111;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0111",
      "origname" => "m0111",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0112;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0112",
      "origname" => "m0112",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "MAN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0113;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0113",
      "origname" => "m0113",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "POW")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0114;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0114",
      "origname" => "m0114",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_BRGTRAY = $self->{features}->getString("BRGTRAY");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_BRGTRAY eq "RLLR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0115;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0115",
      "origname" => "m0115",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "FIX")) ) {
    $s_validate = 0;
  }
  if( !(($f_CONTRAY eq "NO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0116;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0116",
      "origname" => "m0116",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");
  my $f_RHWALK = $self->{features}->getString("RHWALK");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_CONTRAY eq "FOLD")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0117;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0117",
      "origname" => "m0117",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( ((((($f_CONFIG eq "STD")) && (($f_RHWALK eq "SLD")))) || (((($f_CONFIG eq "STD")) && (((($f_CONTRAY eq "SLD")) || (($f_CONTRAY eq "POW"))))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0118;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0118",
      "origname" => "m0118",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0119;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0119",
      "origname" => "m0119",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "POW")) ) {
    $s_validate = 0;
  }
  if( !(($f_CONTRAY eq "NO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0120;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0120",
      "origname" => "m0120",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0121;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0121",
      "origname" => "m0121",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0122;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0122",
      "origname" => "m0122",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_OPCONS eq "FIX")) || (($f_OPCONS eq "POW")))) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0123;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0123",
      "origname" => "m0123",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "FIX")) ) {
    $s_validate = 0;
  }
  if( !(($f_CONTRAY eq "NO")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0124;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0124",
      "origname" => "m0124",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");
  my $f_RHWALK = $self->{features}->getString("RHWALK");

  if( ((((($f_CONFIG eq "COM")) && (((($f_CONTRAY eq "MAN")) || (($f_CONTRAY eq "POW")))))) || (((($f_CONFIG eq "COM")) && (($f_RHWALK eq "SLD"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0125;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0125",
      "origname" => "m0125",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "COM")) && (($f_OPCONS eq "POW")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0126;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0126",
      "origname" => "m0126",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "POW")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0127;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0127",
      "origname" => "m0127",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");
  my $f_OPCONS = $self->{features}->getString("OPCONS");

  if( ((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM"))) ) {
    if( ((((((($f_RHWALK eq "MAN")) || (($f_RHWALK eq "POW")))) && (((($f_CONTRAY eq "MAN")) || (($f_CONTRAY eq "POW")))))) || (($f_OPCONS eq "POW"))) ) {
      $s_validate = 1;
    }
    else {
      $s_validate = 0;
    }
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0128;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0128",
      "origname" => "m0128",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "SLD")) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0129;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0129",
      "origname" => "m0129",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_GUARDR = $self->{features}->getString("GUARDR");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( ((((((($f_CONFIG eq "STD")) && (($f_OPCONS eq "POW")))) && (($f_GUARDR eq "SWING")))) || (((((($f_CONFIG eq "STD")) && (($f_CONTRAY eq "POW")))) && (($f_GUARDR eq "SWING"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0130;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0130",
      "origname" => "m0130",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(($f_CONFIG eq "STD")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "POW")) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0131;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0131",
      "origname" => "m0131",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "FIX")) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0132;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0132",
      "origname" => "m0132",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "SLD")) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0133;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0133",
      "origname" => "m0133",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_GUARDR = $self->{features}->getString("GUARDR");
  my $f_CONTRAY = $self->{features}->getString("CONTRAY");

  if( ((((((($f_CONFIG eq "COM")) && (($f_OPCONS eq "POW")))) && (($f_GUARDR eq "SWING")))) || (((((($f_CONFIG eq "COM")) && (($f_CONTRAY eq "POW")))) && (($f_GUARDR eq "SWING"))))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0134;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0134",
      "origname" => "m0134",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_RHWALK = $self->{features}->getString("RHWALK");
  my $f_GUARDR = $self->{features}->getString("GUARDR");

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }
  if( !(($f_RHWALK eq "POW")) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0135;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0135",
      "origname" => "m0135",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0136;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0136",
      "origname" => "m0136",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "SWING")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0137;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0137",
      "origname" => "m0137",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "NFG")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0138;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0138",
      "origname" => "m0138",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_GUARDR eq "LDH")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0139;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0139",
      "origname" => "m0139",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0140;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0140",
      "origname" => "m0140",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0141;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0141",
      "origname" => "m0141",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0142;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0142",
      "origname" => "m0142",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0143;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0143",
      "origname" => "m0143",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0144;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0144",
      "origname" => "m0144",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0145;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0145",
      "origname" => "m0145",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0146;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0146",
      "origname" => "m0146",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0147;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0147",
      "origname" => "m0147",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0148;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0148",
      "origname" => "m0148",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0149;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0149",
      "origname" => "m0149",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0150;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0150",
      "origname" => "m0150",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P4")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0151;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0151",
      "origname" => "m0151",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P6")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0152;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0152",
      "origname" => "m0152",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P8")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0153;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0153",
      "origname" => "m0153",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0154;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0154",
      "origname" => "m0154",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P4")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0155;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0155",
      "origname" => "m0155",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P6")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0156;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0156",
      "origname" => "m0156",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P8")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0157;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0157",
      "origname" => "m0157",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0158;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0158",
      "origname" => "m0158",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P4")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0159;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0159",
      "origname" => "m0159",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P6")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0160;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0160",
      "origname" => "m0160",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P8")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0161;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0161",
      "origname" => "m0161",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0162;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0162",
      "origname" => "m0162",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P4")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0163;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0163",
      "origname" => "m0163",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P6")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0164;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0164",
      "origname" => "m0164",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P8")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0165;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0165",
      "origname" => "m0165",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P0")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0166;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0166",
      "origname" => "m0166",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P4")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0167;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0167",
      "origname" => "m0167",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P6")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0168;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0168",
      "origname" => "m0168",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "P8")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0169;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0169",
      "origname" => "m0169",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "EL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0170;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0170",
      "origname" => "m0170",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0171;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0171",
      "origname" => "m0171",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0172;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0172",
      "origname" => "m0172",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "DR")) ) {
    $s_validate = 0;
  }
  if( !(($f_PALLSTOP eq "STD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0173;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0173",
      "origname" => "m0173",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "DR")) ) {
    $s_validate = 0;
  }
  if( !(($f_PALLSTOP eq "STD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0174;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0174",
      "origname" => "m0174",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "DR")) ) {
    $s_validate = 0;
  }
  if( !(($f_PALLSTOP eq "STD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0175;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0175",
      "origname" => "m0175",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "DR")) ) {
    $s_validate = 0;
  }
  if( !(($f_PALLSTOP eq "FOR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0176;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0176",
      "origname" => "m0176",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( !(((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "DR")) ) {
    $s_validate = 0;
  }
  if( !(($f_PALLSTOP eq "FOR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0177;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0177",
      "origname" => "m0177",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_PALLSTOP = $self->{features}->getString("PALLSTOP");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "DR")) ) {
    $s_validate = 0;
  }
  if( !(($f_PALLSTOP eq "FOR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0178;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0178",
      "origname" => "m0178",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ELEV eq "EL")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0179;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0179",
      "origname" => "m0179",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZREFLECT = $self->{features}->getString("ZREFLECT");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZREFLECT eq "SILVRED")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0180;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0180",
      "origname" => "m0180",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZREFLECT = $self->{features}->getString("ZREFLECT");

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZREFLECT eq "SILVRED")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0181;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0181",
      "origname" => "m0181",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LANGU eq "EN")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0182;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0182",
      "origname" => "m0182",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LANGU eq "FR")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0183;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0183",
      "origname" => "m0183",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LANGU eq "SP")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0184;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0184",
      "origname" => "m0184",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0185;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0185",
      "origname" => "m0185",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_LANGU eq "PT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0186;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0186",
      "origname" => "m0186",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_WEATHR eq "HOT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0187;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0187",
      "origname" => "m0187",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0188;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0188",
      "origname" => "m0188",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_WEATHR eq "COLD")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0189;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0189",
      "origname" => "m0189",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0190;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0190",
      "origname" => "m0190",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "WID")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0191;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0191",
      "origname" => "m0191",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(($f_CONFIG eq "COM")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0192;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0192",
      "origname" => "m0192",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0193;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0193",
      "origname" => "m0193",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0194;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0194",
      "origname" => "m0194",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
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


package configurator::constraints::IC_TLD_2d838::c_m0195;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0195",
      "origname" => "m0195",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_OILCOOL = $self->{features}->getString("OILCOOL");

  if( !(((((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_OILCOOL eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0196;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0196",
      "origname" => "m0196",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_CUSTOPT = $self->{features}->getString("CUSTOPT");

  if( !(($f_CUSTOPT eq "HAS")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0197;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0197",
      "origname" => "m0197",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_CE828 = $self->{features}->getString("CE828");

  if( !(($f_CE828 eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0198;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0198",
      "origname" => "m0198",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZLOWF = $self->{features}->getString("ZLOWF");

  if( !(($f_ZLOWF eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0199;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0199",
      "origname" => "m0199",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0200;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0200",
      "origname" => "m0200",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZSTAB = $self->{features}->getString("ZSTAB");

  if( !(($f_ZSTAB eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0201;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0201",
      "origname" => "m0201",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZREFLECT = $self->{features}->getString("ZREFLECT");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZREFLECT eq "SILVER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0202;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0202",
      "origname" => "m0202",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZREFLECT = $self->{features}->getString("ZREFLECT");

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZREFLECT eq "SILVER")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0203;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0203",
      "origname" => "m0203",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_PONYA = $self->{features}->getString("PONYA");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_PONYA eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0204;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0204",
      "origname" => "m0204",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_PONYA = $self->{features}->getString("PONYA");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(($f_PONYA eq "Y")) ) {
    $s_validate = 0;
  }
  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0205;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0205",
      "origname" => "m0205",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_PNEUTIRE = $self->{features}->getString("PNEUTIRE");

  if( !(($f_PNEUTIRE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0206;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0206",
      "origname" => "m0206",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZREFLECT = $self->{features}->getString("ZREFLECT");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZREFLECT eq "YBLACK")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0207;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0207",
      "origname" => "m0207",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZREFLECT = $self->{features}->getString("ZREFLECT");

  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZREFLECT eq "YBLACK")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0208;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0208",
      "origname" => "m0208",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZFIREEXT = $self->{features}->getString("ZFIREEXT");

  if( !(($f_ZFIREEXT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0209;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0209",
      "origname" => "m0209",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZAUTOBRK = $self->{features}->getString("ZAUTOBRK");

  if( !(($f_ZAUTOBRK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0210;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0210",
      "origname" => "m0210",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZPARKBRK = $self->{features}->getString("ZPARKBRK");
  my $f_ZAUTOBRK = $self->{features}->getString("ZAUTOBRK");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(((($f_ZPARKBRK eq "Y")) || (($f_ZAUTOBRK eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0211;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0211",
      "origname" => "m0211",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZPARKBRK = $self->{features}->getString("ZPARKBRK");
  my $f_ZAUTOBRK = $self->{features}->getString("ZAUTOBRK");
  my $f_CONFIG = $self->{features}->getString("CONFIG");

  if( !(((($f_ZPARKBRK eq "Y")) || (($f_ZAUTOBRK eq "Y")))) ) {
    $s_validate = 0;
  }
  if( !(((((($f_CONFIG eq "WID")) || (($f_CONFIG eq "UNI")))) || (($f_CONFIG eq "SUP")))) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0212;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0212",
      "origname" => "m0212",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZMLIGHT = $self->{features}->getString("ZMLIGHT");

  if( !(($f_ZMLIGHT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0213;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0213",
      "origname" => "m0213",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZINTLOCK = $self->{features}->getString("ZINTLOCK");

  if( !(((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZINTLOCK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0214;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0214",
      "origname" => "m0214",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZINTLOCK = $self->{features}->getString("ZINTLOCK");

  if( !(($f_CONFIG eq "SUP")) ) {
    $s_validate = 0;
  }
  if( !(($f_ZINTLOCK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0215;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0215",
      "origname" => "m0215",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0216;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0216",
      "origname" => "m0216",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZTOWBAR = $self->{features}->getString("ZTOWBAR");

  if( !(($f_ZTOWBAR eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0217;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0217",
      "origname" => "m0217",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZFRTOW = $self->{features}->getString("ZFRTOW");

  if( !(($f_ZFRTOW eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0218;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0218",
      "origname" => "m0218",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZRRTOW = $self->{features}->getString("ZRRTOW");

  if( !(($f_ZRRTOW eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0219;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0219",
      "origname" => "m0219",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZLOWOIL = $self->{features}->getString("ZLOWOIL");

  if( !(($f_ZLOWOIL eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0220;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0220",
      "origname" => "m0220",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0221;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0221",
      "origname" => "m0221",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZLOWPRES = $self->{features}->getString("ZLOWPRES");

  if( !(($f_ZLOWPRES eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0222;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0222",
      "origname" => "m0222",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZILGAUGE = $self->{features}->getString("ZILGAUGE");

  if( !(((((((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) || (($f_CONFIG eq "WID")))) || (($f_CONFIG eq "UNI")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZILGAUGE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0223;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0223",
      "origname" => "m0223",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZHPUMP = $self->{features}->getString("ZHPUMP");

  if( !(((($f_CONFIG eq "STD")) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZHPUMP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0224;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0224",
      "origname" => "m0224",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZHPUMP = $self->{features}->getString("ZHPUMP");

  if( !(((((($f_CONFIG eq "UNI")) || (($f_CONFIG eq "SUP")))) || (($f_CONFIG eq "COM")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZHPUMP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0225;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0225",
      "origname" => "m0225",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZBRHORN = $self->{features}->getString("ZBRHORN");

  if( !(($f_ZBRHORN eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0226;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0226",
      "origname" => "m0226",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZBATQCK = $self->{features}->getString("ZBATQCK");

  if( !(($f_ZBATQCK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0227;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0227",
      "origname" => "m0227",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZEMSTOP = $self->{features}->getString("ZEMSTOP");

  if( !(($f_ZEMSTOP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0228;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0228",
      "origname" => "m0228",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_Z96TRAYB = $self->{features}->getString("Z96TRAYB");

  if( !(($f_Z96TRAYB eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0229;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0229",
      "origname" => "m0229",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZAUTOSHU = $self->{features}->getString("ZAUTOSHU");

  if( !(($f_ZAUTOSHU eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0230;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0230",
      "origname" => "m0230",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_Z110COLD = $self->{features}->getString("Z110COLD");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_Z110COLD eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0231;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0231",
      "origname" => "m0231",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_Z220COLD = $self->{features}->getString("Z220COLD");

  if( !(($f_ENGINE eq "DU")) ) {
    $s_validate = 0;
  }
  if( !(($f_Z220COLD eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0232;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0232",
      "origname" => "m0232",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_Z220COLD = $self->{features}->getString("Z220COLD");

  if( !(($f_ENGINE eq "CAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_Z220COLD eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0233;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0233",
      "origname" => "m0233",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_Z110COLD = $self->{features}->getString("Z110COLD");

  if( !(($f_ENGINE eq "CAT")) ) {
    $s_validate = 0;
  }
  if( !(($f_Z110COLD eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0234;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0234",
      "origname" => "m0234",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0235;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0235",
      "origname" => "m0235",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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
  my $f_ZSPARK = $self->{features}->getString("ZSPARK");

  if( !(((($f_ENGINE eq "CAT")) || (($f_ENGINE eq "DU4")))) ) {
    $s_validate = 0;
  }
  if( !(($f_ZSPARK eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0236;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0236",
      "origname" => "m0236",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZARMBUMP = $self->{features}->getString("ZARMBUMP");

  if( !(($f_ZARMBUMP eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0237;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0237",
      "origname" => "m0237",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0238;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0238",
      "origname" => "m0238",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0239;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0239",
      "origname" => "m0239",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0240;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0240",
      "origname" => "m0240",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZINFRDET = $self->{features}->getString("ZINFRDET");

  if( !(($f_ZINFRDET eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0241;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0241",
      "origname" => "m0241",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0242;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0242",
      "origname" => "m0242",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZRRFLLGT = $self->{features}->getString("ZRRFLLGT");

  if( !(($f_ZRRFLLGT eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0243;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0243",
      "origname" => "m0243",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZEXTGUID = $self->{features}->getString("ZEXTGUID");

  if( !(($f_ZEXTGUID eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0244;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0244",
      "origname" => "m0244",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZBEACUND = $self->{features}->getString("ZBEACUND");

  if( !(($f_ZBEACUND eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0245;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0245",
      "origname" => "m0245",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0246;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0246",
      "origname" => "m0246",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0247;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0247",
      "origname" => "m0247",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
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


package configurator::constraints::IC_TLD_2d838::c_m0248;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0248",
      "origname" => "m0248",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_OPCONS = $self->{features}->getString("OPCONS");
  my $f_ZFLELEV = $self->{features}->getString("ZFLELEV");

  if( !(($f_OPCONS eq "FIX")) ) {
    $s_validate = 0;
  }
  if( !(($f_ZFLELEV eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0249;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0249",
      "origname" => "m0249",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_OPCONS = $self->{features}->getString("OPCONS");
  my $f_ZFLELEV = $self->{features}->getString("ZFLELEV");

  if( !(($f_OPCONS eq "POW")) ) {
    $s_validate = 0;
  }
  if( !(($f_ZFLELEV eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0250;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0250",
      "origname" => "m0250",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZELEVCOV = $self->{features}->getString("ZELEVCOV");

  if( !(($f_ZELEVCOV eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0251;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0251",
      "origname" => "m0251",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZSENSGUI = $self->{features}->getString("ZSENSGUI");

  if( !(($f_ZSENSGUI eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0252;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0252",
      "origname" => "m0252",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZSIDINTL = $self->{features}->getString("ZSIDINTL");

  if( !(($f_ZSIDINTL eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0253;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0253",
      "origname" => "m0253",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZBEACELE = $self->{features}->getString("ZBEACELE");

  if( !(($f_ZBEACELE eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0254;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0254",
      "origname" => "m0254",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZLHCOLLA = $self->{features}->getString("ZLHCOLLA");

  if( !(($f_ZLHCOLLA eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0255;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0255",
      "origname" => "m0255",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZLHCOLLA = $self->{features}->getString("ZLHCOLLA");

  if( !(($f_ZLHCOLLA eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_TLD_2d838::c_m0256;

use configurator::Functions;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "c_m0256",
      "origname" => "m0256",
      "globals" => $globals,
      "features" => $features,
      };

  bless $self,$class;
  return $self;
}

sub before_input
{
  my $self = shift;
}

sub validation
{
  my $self = shift;
  my $s_validate = $self->{globals}->getInteger("validate");

  my $f_ZLHCOLLA = $self->{features}->getString("ZLHCOLLA");

  if( !(($f_ZLHCOLLA eq "Y")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}

1;
