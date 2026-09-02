package configurator::constraints::IC_TEK_2d300;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_TEK_2d300",
      "item" => "TEK-300",
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

package configurator::constraints::IC_TEK_2d300::c_f001;

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

  my $f_UTILVOLT = $self->{features}->getString("UTILVOLT");
  my $f_UTIL = $self->{features}->getString("UTIL");

  $s_display = 0;
  $s_input = 0;
  $f_UTILVOLT = "";
  if( ($f_UTIL eq "Y") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("UTILVOLT", $f_UTILVOLT);
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


package configurator::constraints::IC_TEK_2d300::c_f002;

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


package configurator::constraints::IC_TEK_2d300::c_f003;

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


package configurator::constraints::IC_TEK_2d300::c_f004;

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


package configurator::constraints::IC_TEK_2d300::c_f005;

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


package configurator::constraints::IC_TEK_2d300::c_f006;

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


package configurator::constraints::IC_TEK_2d300::c_f007;

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


package configurator::constraints::IC_TEK_2d300::c_f008;

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


package configurator::constraints::IC_TEK_2d300::c_f009;

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

1;
