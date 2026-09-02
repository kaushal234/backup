use strict;

package util::constraintparser::codegen::PHP;

use util::constraintparser::Expression;
use util::constraintparser::Symbol;
use util::constraintparser::Basic;

use WiseGlobal;

use constant EXPRTYPE_NONE	=>	0;
use constant EXPRTYPE_BOOLEAN	=>	1;
use constant EXPRTYPE_NUMBER	=>	2;
use constant EXPRTYPE_STRING	=>	3;
use constant EXPRTYPE_STATEMENT	=>	4;

use constant INDEND_STRING	=> "  ";

sub new() {
	my $this = shift;
	my $debug = shift || 0;
	my $class = ref($this) || $this;
	my $self = {	
	    code => "PHP",
	    objdir => "/tmp",
	    debug => $debug,
	    stdfuncs => {
		val   => { name => "util_val",
		           type => EXPRTYPE_NUMBER },
		int   => { name => "util_int",
		           type => EXPRTYPE_NUMBER },
		min   => { name => "util_min",
			   type => EXPRTYPE_NUMBER },
		max   => { name => "util_max",
			   type => EXPRTYPE_NUMBER },
		pow   => { name => "util_pow",
			   type => EXPRTYPE_NUMBER },
		sqrt  => { name => "sqrt",
			   type => EXPRTYPE_NUMBER },
		sin   => { name => "sin",
			   type => EXPRTYPE_NUMBER },
		cos   => { name => "cos",
			   type => EXPRTYPE_NUMBER },
		tan   => { name => "util_tan",
			   type => EXPRTYPE_NUMBER },
		asin  => { name => "util_asin",
			   type => EXPRTYPE_NUMBER },
		acos  => { name => "util_acos",
			   type => EXPRTYPE_NUMBER },
		atan  => { name => "atan2",
			   type => EXPRTYPE_NUMBER },
		hsin  => { name => "util_sinh",
			   type => EXPRTYPE_NUMBER },
		hcos  => { name => "util_cosh",
			   type => EXPRTYPE_NUMBER },
		htan  => { name => "util_tanh",
			   type => EXPRTYPE_NUMBER },
		exp   => { name => "exp",
			   type => EXPRTYPE_NUMBER },
		log   => { name => "log",
			   type => EXPRTYPE_NUMBER },
		str   => { name => "util_str",
			   type => EXPRTYPE_STRING },
		len   => { name => "length",
			   type => EXPRTYPE_STRING },
		strip => { name => "util_strip",
			   type => EXPRTYPE_STRING },
		pos   => { name => "index",
			   type => EXPRTYPE_STRING },
		rpos  => { name => "rindex",
			   type => EXPRTYPE_STRING },
		round => { name => "util_round",
			   type => EXPRTYPE_NUMBER },
		date  => { name => "util_date",
			   type => EXPRTYPE_NUMBER },
		edit  => { name => "util_edit",
		           type => EXPRTYPE_STRING },
	},
};

	bless $self,$class;
	$self->checkObjDir;
	return $self;
}

sub generateCode
{
	my ($self,$product,$constrlist) = @_;

	print STDERR "  executing Perl code generator\n" if $self->{debug};
	my $classname = "IC_" . getObjectName(uc $product);
	unless( $constrlist && @$constrlist ) {
	    print STDERR "no contraints passed for code generation\n";
	    return 0;
	}
	return 0 unless $self->{file} = $self->openSourceFile($classname);
	close($self->{file}),return 0
			unless $self->writeHeader($classname, $product);

	my $intconstrlist = [ @$constrlist ];
	while( @$intconstrlist ) {
	    my $constr = { };
	    my $constrname = $intconstrlist->[0]->name;
	    while (@$intconstrlist && 
	           ($constrname eq $intconstrlist->[0]->name)){
		my $type = $intconstrlist->[0]->type;
		$constr->{$type} = shift @$intconstrlist;
	    }
	    close($self->{file}),return 0
		unless $self->writeConstraint($classname,$constrname,$constr);
	}
	close($self->{file}),return 0
			unless $self->writeFooter($classname);
	close($self->{file});
}

sub openSourceFile
{
	my ($self,$classname) = @_;

	my $file = $self->{objdir} . "/" . $classname . ".inc.php";
	unlink $file;
	unless( open INFILE, ">" . $file ) {
	    print STDERR "cannot open file for writing: ", $file, "\n";
	    return undef;
	}
	$file = *INFILE;
	return $file;
}

sub writeHeader
{
	my ($self,$classname,$product) = @_;

	print {$self->{file}}
	    "package configurator::constraints::$classname;\n\n",
	    "sub new\n",
	    "{\n",
	    indend(1), "my (\$this,\$globals,\$features) = \@_;\n\n",
	    indend(1), "my \$class = ref(\$this) || \$this;\n",
	    indend(1), "my \$self = {\n",
	    indend(3), "\"name\" => \"$classname\",\n",
	    indend(3), "\"item\" => \"", $product, "\",\n",
#	    indend(3), "\"pckname\" => \"configurator::constraints::$classname\",\n",
	    indend(3), "\"globals\" => \$globals,\n",
	    indend(3), "\"features\" => \$features,\n",
	    indend(3), "};\n",
	    indend(1), "bless \$self,\$class;\n",
	    indend(1), "return \$self;\n",
	    "}\n\n",
	    "sub getConstraint\n{\n",
	    indend(1), "my (\$self,\$name) = \@_;\n",
	    indend(1), "return undef unless \$name;\n\n",
#	    indend(1), "my \$pckname = \$self->{pckname} . \"::\" . \$name;\n",
	    indend(1), "my \$pckname = ref(\$self) . \"::c_\" . \$name;\n",
	    indend(1), "my \$class = \$pckname->new(\$self->{globals},\$self->{features});\n",
	    indend(1), "return \$class;\n",
	    "}\n";

	return 1;
}

sub writeFooter
{
	my ($self,$classname) = @_;

	print {$self->{file}} "1;\n";

	return 1;
}

sub writeConstraint
{
	my ($self,$classname,$constrname,$constr) = @_;
	my $file = $self->{file};

	print {$file}
	    "\npackage configurator::constraints::$classname", "::c_",
	        getObjectName($constrname), ";\n\n",
	    "use configurator::Functions;\n\n",
	    "sub new\n",
	    "{\n",
	    indend(1), "my (\$this,\$globals,\$features) = \@_;\n\n",
	    indend(1), "my \$class = ref(\$this) || \$this;\n",
	    indend(1), "my \$self = {\n",
	    indend(3), "\"name\" => \"c_", getObjectName($constrname), "\",\n",
	    indend(3), "\"origname\" => \"", $constrname, "\",\n",
# 	    indend(3), "\"pckname\" => \"configurator::constraints::$classname" . "::" . getObjectName($constrname) . "\",\n",
	    indend(3), "\"globals\" => \$globals,\n",
	    indend(3), "\"features\" => \$features,\n",
	    indend(3), "};\n\n",
	    indend(1), "bless \$self,\$class;\n",
	    indend(1), "return \$self;\n",
	    "}\n\n";

	print {$file} "sub before_input\n{\n",
	    indend(1), "my \$self = shift;\n";
	$self->explodeConstraint($constr->{"1"}) if $constr->{"1"};
	print {$file} "}\n\n";

	print {$file} "sub validation\n{\n" .
	    indend(1), "my \$self = shift;\n";
	$self->explodeConstraint($constr->{"2"}) if $constr->{"2"};
	print {$file} "}\n\n";

	print {$file} "sub parameter_substitution\n{\n" .
	    indend(1), "my \$self = shift;\n";
	$self->explodeConstraint($constr->{"3"}) if $constr->{"3"};
	print {$file} "}\n\n";

}

sub explodeConstraint
{
	my ($self,$constraint) = @_;
	my ($sym, $scope, $hasvars, $hasfeatures);
	my $file = $self->{file};

	$self->{symtable} = $constraint->symtable;
	for $scope (undef, $constraint->scope) {
	    for $sym ($self->{symtable}->getSymbolList($scope,
		    SYM_VARIABLE, SYM_REFERENCED)) {
		print {$file} indend(1), 'my ', varName($sym), " = ";
		if ($sym->isGlobal) {
		    print {$file} '$self->{globals}->get';
		    print {$file} "Integer"
		        if ($sym->datatype == DATATYPE_LONG);
		    print {$file} "Double" 
		        if ($sym->datatype == DATATYPE_DOUBLE);
		    print {$file} "String" 
		        if ($sym->datatype == DATATYPE_STRING);
		    print {$file} '("', ($sym->isSystemDefined ? "" : "g_"),
		                  $sym->output_name, '");', "\n";
		} else {
		    if ($sym->datatype == DATATYPE_STRING) {
			print {$file} '"";', "\n";
		    }
		    else {
		        print {$file} "0;\n";
		    }
		}
		$hasvars = 1;
	    }
	}
	print {$file} "\n" if $hasvars;

	for $sym ($self->{symtable}->getSymbolList($constraint->scope, 
	        SYM_FEATURE, SYM_REFERENCED)) {
	    print {$file} indend(1), 'my ', varName($sym),
	         	  ' = $self->{features}->get';
	    print {$file} "Integer" if ($sym->datatype == DATATYPE_LONG);
	    print {$file} "Double" if ($sym->datatype == DATATYPE_DOUBLE);
	    print {$file} "String" if ($sym->datatype == DATATYPE_STRING);
	    print {$file} '("', $sym->output_name, '");', "\n";
	    $hasfeatures = 1;
	}
	print {$file} "\n" if $hasfeatures;

	$self->explodeStatement($_,1) for (@{$constraint->{statements}});
	print {$file} "\n" if @{$constraint->{statements}};

	for $sym ($self->{symtable}->getSymbolList($constraint->scope, 
	        SYM_FEATURE, SYM_ASSIGNED_TO)) {
	    print {$file} indend(1), '$self->{features}->set("', 
		$sym->output_name, '", ', varName($sym), ");\n";
	}
	for $scope (undef, $constraint->scope) {
	    for $sym ($self->{symtable}->getSymbolList($scope,
		    SYM_GLOBAL, SYM_VARIABLE, SYM_ASSIGNED_TO)) {
		print {$file} indend(1), '$self->{globals}->set("', 
		    ($sym->isSystemDefined ? "" : "g_"),
		    $sym->output_name, '", ', varName($sym), ");\n";
	    }
	}
}

sub explodeStatement
{
	my ($self,$stmt,$indend) = @_;
	if( $stmt->isa("util::constraintparser::Condition") ) {
	    $self->explodeCondition($stmt,$indend);
	}
	else {
	    $self->explodeExpression($stmt,$indend,EXPRTYPE_STATEMENT);
	}
}

sub explodeCondition
{
	my ($self,$cond,$indend) = @_;
	my $file = $self->{file};

	print {$file} indend($indend), "if( ";
	my $res = $self->explodeExpression($cond->{condition},
				-1,EXPRTYPE_BOOLEAN);
	if( $res == EXPRTYPE_STRING ) {
	    print {$file} " ne \"\"";
	}
	elsif( $res == EXPRTYPE_NUMBER ) {
	    print {$file} " != 0";
	}
	print {$file} " ) {\n";
	for my $stmt ( @{$cond->{truepart}} ) {
	    $self->explodeStatement($stmt,$indend+1);
	}
	if( $cond->{falsepart} && @{$cond->{falsepart}} ) {
	    print {$file} indend($indend), "}\n", 
			  indend($indend), "else {\n";
	    for my $stmt ( @{$cond->{falsepart}} ) {
		$self->explodeStatement($stmt,$indend+1);
	    }
	}
	print {$file} indend($indend), "}\n";
}

sub explodeExpression
{
	my ($self,$expr,$indend,$expecttype) = @_;
	my $resulttype;

	my $file = $self->{file};

	print {$file} indend($indend) if $indend > 0;
	if( $expr->{operator} == OPERATOR_ASSIGN ) {
	    if (($expecttype == EXPRTYPE_NUMBER) || 
	        ($expecttype == EXPRTYPE_STRING) || 
	        ($expecttype == EXPRTYPE_BOOLEAN) ) {
		die "didn't expect assignment here";
	    }
	    my $lh = $expr->{operand1};
	    if( $lh->{operator} != OPERATOR_BASICREF ) {
		die "left hand operand not a basic type";
	    }
	    $lh = $lh->{operand1};
	    die "left hand operand not a basic type\n"
	        unless $lh->{type} == BASICTYPE_VARREF;
	    if ($lh->{subscriptstart}) {
		print {$file} "set_substr(\\";
		$self->explodeVarRef($lh, 1);
		print {$file} ",";
		$self->explodeExpression($expr->{operand2},-1,0);
		print {$file} ",";
		$self->explodeExpression($lh->{subscriptstart},-1,
		    EXPRTYPE_NUMBER);
		if ($lh->{subscriptend}) {
		    print {$file} ",";
		    $self->explodeExpression($lh->{subscriptend},-1,
			EXPRTYPE_NUMBER);
	        }
		print {$file} ")";
	    }
	    else {
		$self->explodeVarRef($lh);
		print {$file} " = ";
		if ($lh->datatype == DATATYPE_LONG &&
		        $expr->{operand2}{datatype} == DATATYPE_DOUBLE) {
		    print {$file} "int(";
		    $self->explodeExpression($expr->{operand2},-1,0);
		    print {$file} ")";
		}
		else {
		    $self->explodeExpression($expr->{operand2},-1,0);
		}
	    }
	    print {$file} ";\n";
	    return EXPRTYPE_NONE;
	}
	elsif( $expr->{operator} == OPERATOR_BASICREF ) {
	    my $res = EXPRTYPE_NONE;
	    my $ref = $expr->{operand1};
	    if( $ref->{type} == BASICTYPE_VARREF ) {
		$self->explodeVarRef($ref);
		if ($ref->{symbol}{datatype} == DATATYPE_STRING) {
		    return EXPRTYPE_STRING;
		}
		return EXPRTYPE_NUMBER;
	    }
	    elsif( $ref->{type} == BASICTYPE_STATIC ) {
		if( $ref->{datatype} == DATATYPE_STRING ) {
		    print {$file} "\"", $ref->{value} . "\"";
		    return EXPRTYPE_STRING;
		}
		print {$file} $ref->{value};
		return EXPRTYPE_NUMBER;
	    }
	    elsif( $ref->{type} == BASICTYPE_FUNCTION ) {
		die "missing Perl function definition for: ", 
		    $ref->{symbol}{name}
		    unless defined $self->{stdfuncs}{$ref->{symbol}{name}};
		print {$file} $self->{stdfuncs}{$ref->{symbol}{name}}{name},
		    "(";
		my $septoprint = 0;
		for my $parm ( @{$ref->{params}} ) {
		    print {$file} "," if $septoprint;
		    $septoprint++;
		    $self->explodeExpression($parm,-1,0);
		}
		print {$file} ")";
		return $self->{stdfuncs}{$ref->{symbol}{name}}{type};
	    }
	    return EXPRTYPE_NONE;
	}
	elsif(	($expr->{operator} == OPERATOR_OR) ||
			($expr->{operator} == OPERATOR_AND) ) {
	    print {$file} "(";
	    if( defined($expecttype) &&
		    (($expecttype == EXPRTYPE_NUMBER) ||
			    ($expecttype == EXPRTYPE_STRING)) ) {
		print {$file} "(";
	    }
	    print {$file} "(";
	    my $res = $self->explodeExpression($expr->{operand1},
				    -1,EXPRTYPE_BOOLEAN);
	    if( $res == EXPRTYPE_NUMBER ) {
		print {$file} " != 0";
	    }
	    elsif( $res == EXPRTYPE_STRING ) {
		print {$file} " ne \"\"";
	    }
	    print {$file} ")";
	    if( $expr->{operator} == OPERATOR_OR ) {
		print {$file} " || ";
	    }
	    else {
		print {$file} " && ";
	    }
	    print {$file} "(";
	    $res = $self->explodeExpression($expr->{operand2},
				    -1,EXPRTYPE_BOOLEAN);
	    if( $res == EXPRTYPE_NUMBER ) {
		print {$file} " != 0";
	    }
	    elsif( $res == EXPRTYPE_STRING ) {
		print {$file} " ne \"\"";
	    }
	    print {$file} ")";
	    if( defined($expecttype) &&
		    (($expecttype == EXPRTYPE_NUMBER) ||
			    ($expecttype == EXPRTYPE_STRING)) ) {
		print {$file} ") ? ";
		if( $expecttype == EXPRTYPE_NUMBER ) {
			print {$file} "1 : 0";
		}
		else {
			print {$file} "\"true\" : \"\"";
		}
	    }
	    print {$file} ")";
	    return EXPRTYPE_BOOLEAN;
	}
	elsif( $expr->{operator} == OPERATOR_NOT ) {
	    print {$file} "!(";
	    my $res = $self->explodeExpression($expr->{operand1},
				    -1,EXPRTYPE_BOOLEAN);
	    if( $res == EXPRTYPE_NUMBER ) {
		print {$file} " != 0";
	    }
	    elsif( $res == EXPRTYPE_STRING ) {
		print {$file} " ne \"\"";
	    }
	    print {$file} ")";
	    return EXPRTYPE_BOOLEAN;
	}
	elsif (($expr->{operator} == OPERATOR_ADD) ||
	       ($expr->{operator} == OPERATOR_SUB) ||
	       ($expr->{operator} == OPERATOR_DIV) ||
	       ($expr->{operator} == OPERATOR_MULT) ||
	       ($expr->{operator} == OPERATOR_REMAINDER) ) {
	    print {$file} "(";
	    $self->explodeExpression($expr->{operand1},-1,EXPRTYPE_NUMBER);
	    if(	$expr->{operator} == OPERATOR_ADD ) {
		print {$file} " + ";
	    }
	    elsif( $expr->{operator} == OPERATOR_SUB ) {
		print {$file} " - ";
	    }
	    elsif( $expr->{operator} == OPERATOR_DIV ) {
		print {$file} " / ";
	    }
	    elsif( $expr->{operator} == OPERATOR_MULT ) {
		print {$file} " * ";
	    }
	    elsif( $expr->{operator} == OPERATOR_REMAINDER ) {
		print {$file} " % ";
	    }
	    $self->explodeExpression($expr->{operand2},-1,EXPRTYPE_NUMBER);
	    print {$file} ")";
	    return EXPRTYPE_NUMBER;
	}
	elsif ($expr->{operator} == OPERATOR_NEG) {
	    print {$file} "-";
	    $self->paren1($expr->{operand1}, "-");
	    $self->explodeExpression($expr->{operand1},-1,EXPRTYPE_NUMBER);
	    $self->paren2($expr->{operand1});
	    return EXPRTYPE_NUMBER;
	}
	elsif (($expr->{operator} == OPERATOR_EQ) ||
	       ($expr->{operator} == OPERATOR_NE) ||
	       ($expr->{operator} == OPERATOR_GT) ||
	       ($expr->{operator} == OPERATOR_GE) ||
	       ($expr->{operator} == OPERATOR_LT) ||
	       ($expr->{operator} == OPERATOR_LE) ) {
	    print {$file} "(";
	    my $res = $self->explodeExpression($expr->{operand1},-1,0);
	    if( $res == EXPRTYPE_STRING ) {
		if(	$expr->{operator} == OPERATOR_EQ ) {
		    print {$file} " eq ";
		}
		elsif( $expr->{operator} == OPERATOR_NE ) {
		    print {$file} " ne ";
		}
		elsif( $expr->{operator} == OPERATOR_GT ) {
		    print {$file} " gt ";
		}
		elsif( $expr->{operator} == OPERATOR_GE ) {
		    print {$file} " ge ";
		}
		elsif( $expr->{operator} == OPERATOR_LT ) {
		    print {$file} " lt ";
		}
		else {
		    print {$file} " le ";
		}
		$self->explodeExpression($expr->{operand2},-1,EXPRTYPE_STRING);
		print {$file} ")";
		return EXPRTYPE_BOOLEAN;
	    }
	    if(	$expr->{operator} == OPERATOR_EQ ) {
		print {$file} " == ";
	    }
	    elsif( $expr->{operator} == OPERATOR_NE ) {
		print {$file} " != ";
	    }
	    elsif( $expr->{operator} == OPERATOR_GT ) {
		print {$file} " > ";
	    }
	    elsif( $expr->{operator} == OPERATOR_GE ) {
		print {$file} " >= ";
	    }
	    elsif( $expr->{operator} == OPERATOR_LT ) {
		print {$file} " < ";
	    }
	    else {
		print {$file} " <= ";
	    }
	    $self->explodeExpression($expr->{operand2},-1,$res);
	    print {$file} ")";
	    return EXPRTYPE_BOOLEAN;
	}
	elsif ($expr->{operator} == OPERATOR_CONCAT) {
	    print {$file} "(";
	    my $res = $self->explodeExpression($expr->{operand1}, -1,0);
	    die "expected a string expression for concatentation"
		    unless $res == EXPRTYPE_STRING;
	    print {$file} " . ";
	    $self->explodeExpression($expr->{operand2},-1,
		    EXPRTYPE_STRING);
	    print {$file} ")";
	    return EXPRTYPE_STRING;
	}
	else {
	    die "code generator has nothing to do for operator: ", 
	        $expr->operatorName;
	}
}

sub explodeVarRef {
	my $self = shift;
	my $ref = shift;
	my $ignoresub = shift || 0;
	my $file = $self->{file};

	if ($ref->{subscriptstart} && ! $ignoresub) {
	    print {$file} "util_substr(";
	    print {$file} varName($ref->{symbol}), ",";
	    $self->explodeExpression($ref->{subscriptstart},-1,
		EXPRTYPE_NUMBER);
	    if ($ref->{subscriptend}) {
		print {$file} ",";
		$self->explodeExpression($ref->{subscriptend},-1,
		    EXPRTYPE_NUMBER);
	    }
	    print {$file} ")";
	} else {
	    print {$file} varName($ref->{symbol});
	}
}

sub paren1 {
	my ($self, $operand) = @_;
	return if $operand->{operator} == OPERATOR_BASICREF;
        print {$self->{file}} "(";
}

sub paren2 {
	my ($self, $operand) = @_;
	return if $operand->{operator} == OPERATOR_BASICREF;
        print {$self->{file}} ")";
}

sub checkObjDir
{
	my ($self) = @_;

	my $dir = $global{path}->[0];
	mkdir $dir unless( -e $dir );
	for ( qw(lib configurator constraints) ) {
		$dir .= "/" . $_;
		mkdir $dir unless( -e $dir );
	}
	$self->{objdir} = $global{path}->[0] . "/lib/configurator/constraints";
}

## Non-method functions

sub indend { INDEND_STRING x $_[0]; }

sub getObjectName {
	my $name = shift;
	my $objname;

	($objname = $name) =~ s/\s+$//;
	$objname =~ s/_/__/g;
	$objname =~ s/([^\d\w_])/sprintf("_%x", ord($1))/ge;
	return $objname;
}

sub varName {
	my $sym = shift;
	my ($prefix);

	if ($sym->isFeature) {
	    $prefix = '$f_';
	}
	elsif ($sym->isGlobal) {
	    if ($sym->isSystemDefined) {
	        $prefix = '$s_';
	    }
	    else {
	        $prefix = '$g_';
	    }
	}
	else {
	    $prefix = '$l_';
	}

	return $prefix . $sym->output_name;
}

1;
