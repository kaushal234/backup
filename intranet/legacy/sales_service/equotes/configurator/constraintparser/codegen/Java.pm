# *****************************************************************************
# Author  : Bernd Loske & Kevin Brock
# Date    : June 16, 2002
# Comments:
#   This script is part of a utility to parse Baan constraints and
#	generate programs in other languages
#
# $Id: Java.pm,v 2.1 2003/05/01 05:53:33 kbrock Exp $
#
# =============================================================================
# - Copyright (C) 2000-2002 Fullscope.  All Rights Reserved.                  -
# -     								      -
# - This software program contains proprietary technology and trade           -
# - secrets developed by Fullscope through substantial creative effort.       -
# - No part of this software may be duplicated, reused, or disclosed          -
# - without a duly authorized license agreement and/or written permission     -
# - from Fullscope.                                                           -
# =============================================================================
#

use strict;

package util::constraintparser::codegen::Java;

use util::constraintparser::Expression;
use util::constraintparser::Symbol;
use util::constraintparser::Basic;

use WiseGlobal;
use WiseUtil;

use constant EXPRTYPE_NONE	=>	0;
use constant EXPRTYPE_BOOLEAN	=>	1;
use constant EXPRTYPE_NUMBER	=>	2;
use constant EXPRTYPE_STRING	=>	3;
use constant EXPRTYPE_STATEMENT	=>	4;

use constant DATATYPE_PARMS	=>	100;

use constant INDEND_STRING	=> "  ";

sub new() {
	my $this = shift;
	my $debug = shift || 0;
	my $class = ref($this) || $this;

	my $cmd = $cfg{configurator}{codegen}{java}{compiler} or
	    javaCmdError("No java compiler defined in configurator.cfg");
	javaCmdError("compiler string missing '{classpath}' parameter")
	    unless $cmd =~ /{classpath}/i;
	javaCmdError("compiler string missing '{objdir}' parameter")
	    unless $cmd =~ /{objdir}/i;
	javaCmdError("compiler string missing '{srcpath}' parameter")
	    unless $cmd =~ /{srcpath}/i;

	my $self = {
	    code => "Java",
	    objdir => "",
	    compiler => $cmd,
	    srcdir => $cfg{configurator}{codegen}{java}{srcdir} || "/tmp",
	    srckeep => $cfg{configurator}{codegen}{java}{srckeep} || 0,
	    debug => $debug,

	    stdfuncs => {
		val   => { name => "ConstrFunctions.parseDouble",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		int   => { name => "(int) ",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_LONG },
		min   => { name => "Math.min",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_PARMS },
		max   => { name => "Math.min",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_PARMS },
		pow   => { name => "Math.pow",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_PARMS },
		sqrt  => { name => "Math.sqrt",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		sin   => { name => "Math.sin",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		cos   => { name => "Math.cos",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		tan   => { name => "Math.tan",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		asin  => { name => "Math.asin",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		acos  => { name => "Math.acos",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		atan  => { name => "Math.atan",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		hsin  => { name => "ConstrFunctions.sinh",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		hcos  => { name => "ConstrFunctions.cosh",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		htan  => { name => "ConstrFunctions.tanh",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		exp   => { name => "Math.exp",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		log   => { name => "Math.log",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_DOUBLE },
		str   => { name => "String.valueOf",
			   type => EXPRTYPE_STRING,
			   datatype => DATATYPE_STRING },
		len   => { name => "ConstrFunctions.len",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_LONG },
		strip => { name => "ConstrFunctions.strip",
			   type => EXPRTYPE_STRING,
			   datatype => DATATYPE_STRING },
		pos   => { name => "ConstrFunctions.pos",
			   type => EXPRTYPE_STRING,
			   datatype => DATATYPE_LONG },
		rpos  => { name => "ConstrFunctions.rpos",
			   type => EXPRTYPE_STRING,
			   datatype => DATATYPE_LONG },
		round => { name => "ConstrFunctions.round",
		           type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_PARMS },
		date  => { name => "ConstrFunctions.date",
			   type => EXPRTYPE_NUMBER,
			   datatype => DATATYPE_LONG },
		edit  => { name => "ConstrFunctions.edit",
			   type => EXPRTYPE_STRING,
			   datatype => DATATYPE_STRING },
	    },
	};

	# Ensure the work directory exists
	unless (-d $self->{srcdir}) {
	    mkdir $self->{srcdir}, 0777;
	    die "Source work directory cannot be accessed"
	        unless -d $self->{srcdir};
	}

	bless $self,$class;
	$self->checkObjDir;
	return $self;
}

sub generateCode
{
	my ($self,$product,$constrlist) = @_;

	print STDERR "  executing Java code generator\n" if $self->{debug};
	my $configjar = $self->findConfigurator;
	unless( $configjar ) {
	    print STDERR "cannot compile code => generation aborted\n";
	    return undef;
	}
	my $classname = "IC_" . getObjectName(uc $product);
	return 0 unless( $constrlist && @$constrlist );
	return 0 unless $self->{file} = $self->openSourceFile($classname);
	close($self->{file}),return 0 
	    unless $self->writeHeader($classname);

	$self->removeClassFiles($classname);

	my $intconstrlist = [ @$constrlist ];
	while( @$intconstrlist ) {
	    my $constr = { };
	    my $constrname = $intconstrlist->[0]->name;
	    while( @$intconstrlist && 
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

	my $cmd = $self->{compiler};
	$cmd =~ s/{classpath}/$configjar/i;
	$cmd =~ s/{objdir}/$self->{objdir}/i;
	$cmd =~ s/{srcpath}/$self->{srcdir}\/$classname.java/i;
	print "  Executing: $cmd\n" if $self->{debug};
	system($cmd);

	unlink "$self->{srcdir}/$classname.java" unless $self->{srckeep};
}

sub openSourceFile
{
	my ($self,$classname) = @_;

	my $file = $self->{srcdir} . "/" . $classname . ".java";
	unlink $file;
	unless( open INFILE, ">" . $file) {
	    print STDERR "cannot open file for writing: ", $file, "\n";
	    return undef;
	}
	$file = *INFILE;
	return $file;
}

sub writeHeader
{
	my ($self,$classname) = @_;

	print {$self->{file}}
	    "package com.fullscope.configurator.constraints;\n\n",
	    "import com.fullscope.configurator.ItemConstraints;\n",
	    "import com.fullscope.configurator.ConstraintIF;\n",
	    "import com.fullscope.configurator.ConstrFunctions;\n\n",
	    "public class $classname extends ItemConstraints {\n\n",
	    indend(1),  "public $classname() {\n",
	    indend(2), "super();\n",
	    indend(1), "}\n";

	return 1;
}

sub writeFooter
{
	my ($self,$classname) = @_;

	print {$self->{file}} "}\n";

	return 1;
}

sub writeConstraint
{
	my ($self,$classname,$constrname,$constr) = @_;
	my $file = $self->{file};

	print {$file} "\n", indend(1),
	    "public static class c_", getObjectName($constrname), 
	    " implements ConstraintIF {\n";
	print {$file} indend(2),  "public void beforeInput() {\n";
	if( $constr->{"1"} ) {
	    $self->explodeConstraint($constr->{"1"});
	}
	print {$file} indend(2), "}\n\n";
	print {$file} indend(2), "public void validation() {\n";
	if( $constr->{"2"} ) {
	    $self->explodeConstraint($constr->{"2"});
	}
	print {$file} indend(2), "}\n\n";
	print {$file} indend(2),
	    "public void parameterSubstitution() {\n";
	if( $constr->{"3"} ) {
	    $self->explodeConstraint($constr->{"3"});
	}
	print {$file} indend(2), "}\n";

	print {$file} indend(1), "}\n";
}

sub explodeConstraint
{
	my ($self,$constraint) = @_;
	my ($scope, $sym, $hasvars, $hasfeatures);
	my $file = $self->{file};

	$self->{symtable} = $constraint->symtable;
	for $scope (undef, $constraint->scope) {
	    for $sym ($self->{symtable}->getSymbolList($scope,
		    SYM_VARIABLE, SYM_REFERENCED)) {
		print {$file} indend(3);
		print {$file} "int" if ($sym->datatype == DATATYPE_LONG);
		print {$file} "double" if ($sym->datatype == DATATYPE_DOUBLE);
		print {$file} "String" if ($sym->datatype == DATATYPE_STRING);
		print {$file} " ", varName($sym), " = ";
		if ($sym->isGlobal) {
		    print {$file} "globals.get";
		    print {$file} "Integer" 
			    if ($sym->datatype == DATATYPE_LONG);
		    print {$file} "Double" 
		        if ($sym->datatype == DATATYPE_DOUBLE);
		    print {$file} "String" 
		        if ($sym->datatype == DATATYPE_STRING);
		    print {$file} '("', ($sym->isSystemDefined ? "" : "g_"), 
		                  $sym->output_name, '");', "\n";
		} else {
		    print {$file} "0;\n" 
		        if ($sym->datatype == DATATYPE_LONG);
		    print {$file} "0d;\n" 
		        if ($sym->datatype == DATATYPE_DOUBLE);
		    print {$file} '"";', "\n" 
		        if ($sym->datatype == DATATYPE_STRING);
		}
		$hasvars = 1;
	    }
	}
	print {$file} "\n" if $hasvars;

	for $sym ($self->{symtable}->getSymbolList($constraint->scope, 
	        SYM_FEATURE, SYM_REFERENCED)) {
	    print {$file} indend(3);
	    print {$file} "int" if ($sym->datatype == DATATYPE_LONG);
	    print {$file} "double" if ($sym->datatype == DATATYPE_DOUBLE);
	    print {$file} "String" if ($sym->datatype == DATATYPE_STRING);
	    print {$file} " ", varName($sym), ' = features.get("', 
	                  $sym->output_name, '").get';
	    print {$file} "Integer();\n" if ($sym->datatype == DATATYPE_LONG);
	    print {$file} "Double();\n" if ($sym->datatype == DATATYPE_DOUBLE);
	    print {$file} "String();\n" if ($sym->datatype == DATATYPE_STRING);
	    $hasfeatures = 1;
	}
	print {$file} "\n" if $hasfeatures;

	$self->explodeStatement($_,3) for (@{$constraint->{statements}});
	print {$file} "\n" if @{$constraint->{statements}};

	for $sym ($self->{symtable}->getSymbolList($constraint->scope, 
	        SYM_FEATURE, SYM_ASSIGNED_TO)) {
	    print {$file} indend(3), 'features.get("', $sym->output_name,
	        '").set(', varName($sym), ");\n";
	}
	for $scope (undef, $constraint->scope) {
	    for $sym ($self->{symtable}->getSymbolList($scope,
		    SYM_GLOBAL, SYM_VARIABLE, SYM_ASSIGNED_TO)) {
		print {$file} indend(3), 'globals.set("', 
		    ($sym->isSystemDefined ? "" : "g_"), $sym->output_name,
		    '", ', varName($sym), ");\n";
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
	my $res = $self->explodeExpression(
	     $cond->{condition},-1,EXPRTYPE_BOOLEAN);
	if( $res == EXPRTYPE_STRING ) {
	    print {$file} ".compareTo(\"\") != 0";
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
	    $self->explodeVarRef($lh, 1);
	    print {$file} " = ";
	    if ($lh->{subscriptstart}) {
		print {$file} "ConstrFunctions.setSubstring(";
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
		if ($lh->datatype == DATATYPE_LONG &&
		        $expr->{operand2}{datatype} == DATATYPE_DOUBLE) {
		    print {$file} "(int) (";
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
		    # Handle strings broken across multiple lines
		    # which appears to be supported by Baan's
		    # constraint interpreter.
		    $ref->{value} =~ s/\n/\\n/gs;

		    print {$file} "\"", $ref->{value}, "\"";
		    return EXPRTYPE_STRING;
		}
		print {$file} $ref->{value};
		return EXPRTYPE_NUMBER;
	    }
	    elsif( $ref->{type} == BASICTYPE_FUNCTION ) {
		print {$file} 
		    $self->{stdfuncs}{$ref->{symbol}{name}}{name}, "(";
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
		    ( ($expecttype == EXPRTYPE_NUMBER) ||
		      ($expecttype == EXPRTYPE_STRING) ) ) {
		print {$file} "(";
	    }
	    print {$file} "(";
	    my $res = $self->explodeExpression($expr->{operand1},
				    -1,EXPRTYPE_BOOLEAN);
	    if( $res == EXPRTYPE_NUMBER ) {
		print {$file} " != 0";
	    }
	    elsif( $res == EXPRTYPE_STRING ) {
		print {$file} ".compareTo(\"\") != 0";
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
		print {$file} ".compareTo(\"\") != 0";
	    }
	    print {$file} ")";
	    if( defined($expecttype) &&
		    ( ($expecttype == EXPRTYPE_NUMBER) ||
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
		print {$file} ".compareTo(\"\") != 0";
	    }
	    print {$file} ")";
	    return EXPRTYPE_BOOLEAN;
	}
	elsif(	($expr->{operator} == OPERATOR_ADD) ||
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
	    print {$file} "-(";
	    $self->explodeExpression($expr->{operand1},-1,EXPRTYPE_NUMBER);
	    print {$file} ")";
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
		print {$file} ".compareTo(";
		$self->explodeExpression($expr->{operand2},-1,EXPRTYPE_STRING);
		print {$file} ")";
		if(	$expr->{operator} == OPERATOR_EQ ) {
			print {$file} " == 0)";
		}
		elsif( $expr->{operator} == OPERATOR_NE ) {
			print {$file} " != 0)";
		}
		elsif( $expr->{operator} == OPERATOR_GT ) {
			print {$file} " > 0)";
		}
		elsif( $expr->{operator} == OPERATOR_GE ) {
			print {$file} " >= 0)";
		}
		elsif( $expr->{operator} == OPERATOR_LT ) {
			print {$file} " < 0)";
		}
		else {
			print {$file} " <= 0)";
		}
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
	    my $res = $self->explodeExpression($expr->{operand1},
		    -1,0);
	    die "expected a string expression for concatentation"
		    unless $res == EXPRTYPE_STRING;
	    print {$file} " + ";
	    $self->explodeExpression($expr->{operand2},-1,
		    EXPRTYPE_STRING);
	    print {$file} ")";
	    return EXPRTYPE_STRING;
	}
	else {
	    die "code generator has nothing to do for operator: ", 
	        $expr->getOperatorName;
	}
}

sub explodeVarRef {
	my $self = shift;
	my $ref = shift;
	my $ignoresub = shift || 0;
	my $file = $self->{file};

	if ($ref->{subscriptstart} && ! $ignoresub) {
	    print {$file}
		"ConstrFunctions.getSubstring(",
		varName($ref->{symbol}), ",";
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

sub findConfigurator {
	my ($self) = @_;

	#
	# search the script file for the generator
	#
	my $filename = "java/lib/configurator.jar";
	my $path = pathname($filename);
	unless( $path ) {
		print STDERR "  => configurator jar file not found\n";
		return undef;
	}
	return $path;
}

sub checkObjDir {
	my ($self) = @_;

	my $dir = $global{path}->[0];
	mkdir $dir unless( -e $dir );
	for ( qw(java classes com fullscope configurator constraints) ) {
		$dir .= "/" . $_;
		mkdir $dir unless( -e $dir );
	}
	$self->{objdir} = $global{path}->[0] . "/java/classes";
}

sub removeClassFiles {
	my $self = shift;
	my $classname = shift;

	my $dir = $self->{objdir} . "/com/fullscope/configurator/constraints";
	opendir CLASSDIR, $dir or
	    die "unable to open output class directory ($dir)";
	unlink map { $dir . "/" . $_ } 
	    grep { /^\Q$classname\E(?:\$.*?)?\.class$/ } readdir CLASSDIR;
	closedir CLASSDIR;
}

## Non-method functions

sub javaCmdError {
	my $msg = shift;
	print STDERR "\n**** Java code generator fatal error ****\n";
	print STDERR "Configuration file configurator.cfg must have\n";
	print STDERR "an entry for codegen/java/compiler.   This should\n";
	print STDERR "be the command to execute to compile the java\n";
	print STDERR "constraint scripts.  Use these replacement\n";
	print STDERR "indicators:\n";
	print STDERR "    {classpath} - will be replaced with the name\n";
	print STDERR "                  of the configurator jar.\n";
	print STDERR "    {objdir}    - will be replaced with the real\n";
	print STDERR "                  class output directory.\n";
	print STDERR "    {srcpath}   - will be replaced with the name\n";
	print STDERR "                  of the generated source file.\n";
	print STDERR "For example (using Sun's standard SDK 1.3.1):\n";
	print STDERR "    \"javac -g:none -classpath {classpath} -d {objdir} {srcpath}\"\n";
	die $msg;
}

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
	    $prefix = 'f_';
	}
	elsif ($sym->isGlobal) {
	    if ($sym->isSystemDefined) {
	        $prefix = 's_';
	    }
	    else {
	        $prefix = 'g_';
	    }
	}
	else {
	    $prefix = 'l_';
	}

	return $prefix . $sym->output_name;
}

1;
