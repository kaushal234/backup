package configurator::constraints::IC_DOLLY;

sub new
{
  my ($this,$globals,$features) = @_;

  my $class = ref($this) || $this;
  my $self = {
      "name" => "IC_DOLLY",
      "item" => "DOLLY",
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

package configurator::constraints::IC_DOLLY::c_f001;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( ($f_DOLLTYPE eq "BC6521U") ) {
    $s_display = 0;
    $s_input = 0;
    $f_FINISH = "";
  }

  $self->{features}->set("FINISH", $f_FINISH);
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


package configurator::constraints::IC_DOLLY::c_f002;

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

  my $f_TIRES = $self->{features}->getString("TIRES");
  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  $s_display = 0;
  $s_input = 0;
  $f_TIRES = "";
  if( ($f_DOLLTYPE eq "PD2014") ) {
    $s_display = 1;
    $s_input = 1;
  }

  $self->{features}->set("TIRES", $f_TIRES);
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


package configurator::constraints::IC_DOLLY::c_m001;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC1010")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m002;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "BC6601A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m003;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "BC6601B")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m004;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6602")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m005;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD1010")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m006;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD2300")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m007;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "CD2300A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m008;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6146")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m009;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "CD6610")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m010;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "CD6610A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m011;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6620")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m012;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6646")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m013;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CR7730")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m014;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD1014")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m015;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");
  my $f_TIRES = $self->{features}->getString("TIRES");

  if( !(($f_DOLLTYPE eq "PD2014")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }
  if( !(($f_TIRES eq "24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m016;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "PD2014A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m017;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "PD2014C")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m018;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD2200")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m019;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "PD2200A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m020;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD6620")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m021;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "PD6620C")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m022;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "PD6620TG")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m023;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "SP8804A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m024;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "SP8805A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m025;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "SP8806A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m026;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "WS8801")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m027;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "WS8801A")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m028;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "WS8802")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m029;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "WS8803")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m030;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "WS8804")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m031;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "XHT0008")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m032;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");

  if( !(($f_DOLLTYPE eq "XHT00081")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m033;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6520")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m034;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6521")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m035;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC1010")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m036;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6601")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m037;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6601")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m038;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6602")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m039;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6603")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m040;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6603")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m041;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6520")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m042;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6521")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m043;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6522")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m044;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD1010")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m045;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD2300")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m046;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6146")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m047;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6610CE")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m048;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6610CE")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m049;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6610K")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m050;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6610K")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m051;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6620")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m052;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD6646")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m053;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD9610")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m054;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CD9610")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m055;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD1014")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m056;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");
  my $f_TIRES = $self->{features}->getString("TIRES");

  if( !(($f_DOLLTYPE eq "PD2014")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }
  if( !(($f_TIRES eq "16")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m057;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");
  my $f_TIRES = $self->{features}->getString("TIRES");

  if( !(($f_DOLLTYPE eq "PD2014")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }
  if( !(($f_TIRES eq "16")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m058;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");
  my $f_TIRES = $self->{features}->getString("TIRES");

  if( !(($f_DOLLTYPE eq "PD2014")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }
  if( !(($f_TIRES eq "24")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m059;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD2200")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m060;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD6620")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m061;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD7710")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m062;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD7710")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m063;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CR7730")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m064;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CR7733")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m065;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "CR7733")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m067;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "BC6522")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m068;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD1010")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "PAINT")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}


package configurator::constraints::IC_DOLLY::c_m069;

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

  my $f_DOLLTYPE = $self->{features}->getString("DOLLTYPE");
  my $f_FINISH = $self->{features}->getString("FINISH");

  if( !(($f_DOLLTYPE eq "PD1010")) ) {
    $s_validate = 0;
  }
  if( !(($f_FINISH eq "ZINC")) ) {
    $s_validate = 0;
  }

  $self->{globals}->set("validate", $s_validate);
}

sub parameter_substitution
{
  my $self = shift;
}

1;
