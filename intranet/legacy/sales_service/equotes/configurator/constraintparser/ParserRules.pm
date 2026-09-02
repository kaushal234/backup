####################################################################
#
#    This file was generated using Parse::Yapp version 1.05.
#
#        Don't edit this file, use source file instead.
#
#             ANY CHANGE MADE HERE WILL BE LOST !
#
####################################################################
package ParserRules;
use vars qw ( @ISA );
use strict;

@ISA= qw ( Parse::Yapp::Driver );
use Parse::Yapp::Driver;

#line 1 "ParserRules.yp"

# =============================================================================
# File    : ParserRules.yp (generated files is Parser.pm)
# Author  : Bernd Loske & Kevin Brock / Fullscope
# Date    : June 2002
# Comments:
#    Parser grammer rules for WiseB2B configruator constraint parser.
#
# =============================================================================
# $Id: ParserRules.yp,v 2.1 2003/03/04 03:07:44 kbrock Exp $
# =============================================================================
# - Copyright (C) 2000-2002 Fullscope.  All Rights Reserved.                  -
# -     								      -
# - This software program contains proprietary technology and trade           -
# - secrets developed by Fullscope through substantial creative effort.       -
# - No part of this software may be duplicated, reused, or disclosed          -
# - without a duly authorized license agreement and/or written permission     -
# - from Fullscope.                                                           -
# =============================================================================
 
use util::constraintparser::Constraint;
use util::constraintparser::Expression;
use util::constraintparser::Condition;
use util::constraintparser::Basic;
use util::constraintparser::Symbol;


sub new {
        my($class)=shift;
        ref($class)
    and $class=ref($class);

    my($self)=$class->SUPER::new( yyversion => '1.05',
                                  yystates =>
[
	{#State 0
		ACTIONS => {
			'TOK_LONG' => 4,
			'TOK_STRING' => 2,
			'TOK_DOUBLE' => 5
		},
		DEFAULT => -4,
		GOTOS => {
			'vardecl' => 1,
			'grammar' => 3,
			'vardecls' => 6,
			'datatype' => 7
		}
	},
	{#State 1
		ACTIONS => {
			'TOK_LONG' => 4,
			'TOK_STRING' => 2,
			'TOK_DOUBLE' => 5
		},
		DEFAULT => -4,
		GOTOS => {
			'vardecl' => 1,
			'vardecls' => 8,
			'datatype' => 7
		}
	},
	{#State 2
		DEFAULT => -9
	},
	{#State 3
		ACTIONS => {
			'' => 9
		}
	},
	{#State 4
		DEFAULT => -7
	},
	{#State 5
		DEFAULT => -8
	},
	{#State 6
		ACTIONS => {
			'NAME' => 10,
			"[" => 20,
			'CONSTRMARKER' => 14,
			'TOK_IF' => 15
		},
		DEFAULT => -2,
		GOTOS => {
			'feature' => 17,
			'grammarstatements' => 16,
			'stdvar' => 11,
			'statement' => 18,
			'assignment' => 13,
			'variable' => 12,
			'shortform' => 19,
			'condition' => 21
		}
	},
	{#State 7
		ACTIONS => {
			'NAME' => 22
		}
	},
	{#State 8
		DEFAULT => -5
	},
	{#State 9
		DEFAULT => 0
	},
	{#State 10
		ACTIONS => {
			"(" => 24
		},
		DEFAULT => -50,
		GOTOS => {
			'subscript' => 23
		}
	},
	{#State 11
		DEFAULT => -46
	},
	{#State 12
		ACTIONS => {
			"=" => 25
		}
	},
	{#State 13
		DEFAULT => -65
	},
	{#State 14
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 32,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 15
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 41,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 16
		DEFAULT => -1
	},
	{#State 17
		DEFAULT => -47
	},
	{#State 18
		ACTIONS => {
			'NAME' => 10,
			'CONSTRMARKER' => 14,
			"[" => 20,
			'TOK_IF' => 15
		},
		DEFAULT => -2,
		GOTOS => {
			'grammarstatements' => 42,
			'feature' => 17,
			'stdvar' => 11,
			'assignment' => 13,
			'statement' => 18,
			'variable' => 12,
			'shortform' => 19,
			'condition' => 21
		}
	},
	{#State 19
		DEFAULT => -66
	},
	{#State 20
		ACTIONS => {
			'NAME' => 43
		}
	},
	{#State 21
		DEFAULT => -67
	},
	{#State 22
		ACTIONS => {
			'TOK_GLOBAL' => 45
		},
		DEFAULT => -10,
		GOTOS => {
			'globaldecl' => 44
		}
	},
	{#State 23
		DEFAULT => -48
	},
	{#State 24
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 46,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 25
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 47,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 26
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 48,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 27
		DEFAULT => -35
	},
	{#State 28
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 49,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 29
		DEFAULT => -56
	},
	{#State 30
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 50,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 31
		DEFAULT => -58
	},
	{#State 32
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			'OR' => 62,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -12
	},
	{#State 33
		DEFAULT => -57
	},
	{#State 34
		DEFAULT => -55
	},
	{#State 35
		ACTIONS => {
			"(" => 66
		}
	},
	{#State 36
		DEFAULT => -36
	},
	{#State 37
		DEFAULT => -59
	},
	{#State 38
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 67,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 39
		DEFAULT => -54
	},
	{#State 40
		DEFAULT => -34
	},
	{#State 41
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			'TOK_THEN' => 68,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			'OR' => 62,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		}
	},
	{#State 42
		DEFAULT => -3
	},
	{#State 43
		ACTIONS => {
			"]" => 69
		}
	},
	{#State 44
		DEFAULT => -6
	},
	{#State 45
		DEFAULT => -11
	},
	{#State 46
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			";" => 70,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			'OR' => 62,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -52,
		GOTOS => {
			'subscriptleng' => 71
		}
	},
	{#State 47
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			'OR' => 62,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -68
	},
	{#State 48
		DEFAULT => -15
	},
	{#State 49
		DEFAULT => -16
	},
	{#State 50
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -29
	},
	{#State 51
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 72,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 52
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 73,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 53
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 74,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 54
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 75,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 55
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 76,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 56
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 77,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 57
		ACTIONS => {
			"{" => 79,
			'NOT' => 78
		}
	},
	{#State 58
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 80,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 59
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 81,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 60
		ACTIONS => {
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			'CONST_FALSE' => 31,
			'CONST_STRING' => 39,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'simpleexprlist' => 82,
			'constant' => 36,
			'simpleexpr' => 83
		}
	},
	{#State 61
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 84,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 62
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 85,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 63
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 86,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 64
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 87,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 65
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 88,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 66
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		DEFAULT => -37,
		GOTOS => {
			'stdvar' => 11,
			'variable' => 27,
			'constant' => 36,
			'feature' => 17,
			'condexprlist' => 91,
			'expression' => 89,
			'exprlist' => 90,
			'simpleexpr' => 40
		}
	},
	{#State 67
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			"*" => 55,
			")" => 92,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			'OR' => 62,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		}
	},
	{#State 68
		ACTIONS => {
			'NAME' => 10,
			'CONSTRMARKER' => 14,
			"[" => 20,
			'TOK_IF' => 15
		},
		GOTOS => {
			'feature' => 17,
			'statements' => 93,
			'stdvar' => 11,
			'assignment' => 13,
			'statement' => 94,
			'variable' => 12,
			'shortform' => 19,
			'condition' => 21
		}
	},
	{#State 69
		ACTIONS => {
			"(" => 24
		},
		DEFAULT => -50,
		GOTOS => {
			'subscript' => 95
		}
	},
	{#State 70
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 96,
			'constant' => 36,
			'simpleexpr' => 40
		}
	},
	{#State 71
		ACTIONS => {
			")" => 97
		}
	},
	{#State 72
		ACTIONS => {
			"*" => 55,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -14
	},
	{#State 73
		ACTIONS => {
			"-" => 51,
			"+" => 53,
			"*" => 55,
			"&" => 58,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -25
	},
	{#State 74
		ACTIONS => {
			"*" => 55,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -13
	},
	{#State 75
		ACTIONS => {
			"-" => 51,
			"+" => 53,
			"*" => 55,
			"&" => 58,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -24
	},
	{#State 76
		DEFAULT => -19
	},
	{#State 77
		ACTIONS => {
			"-" => 51,
			"+" => 53,
			"*" => 55,
			"&" => 58,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -22
	},
	{#State 78
		ACTIONS => {
			"{" => 98
		}
	},
	{#State 79
		ACTIONS => {
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			'CONST_FALSE' => 31,
			'CONST_STRING' => 39,
			"[" => 20
		},
		GOTOS => {
			'rangeexprlist' => 99,
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'constant' => 36,
			'simpleexpr' => 100
		}
	},
	{#State 80
		ACTIONS => {
			"*" => 55,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -20
	},
	{#State 81
		DEFAULT => -18
	},
	{#State 82
		DEFAULT => -21
	},
	{#State 83
		ACTIONS => {
			"," => 101
		},
		DEFAULT => -41
	},
	{#State 84
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -27
	},
	{#State 85
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -28
	},
	{#State 86
		ACTIONS => {
			"-" => 51,
			"+" => 53,
			"*" => 55,
			"&" => 58,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -26
	},
	{#State 87
		DEFAULT => -17
	},
	{#State 88
		ACTIONS => {
			"-" => 51,
			"+" => 53,
			"*" => 55,
			"&" => 58,
			"/" => 59,
			"\\" => 64
		},
		DEFAULT => -23
	},
	{#State 89
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			"," => 102,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			'OR' => 62,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -39
	},
	{#State 90
		DEFAULT => -38
	},
	{#State 91
		ACTIONS => {
			")" => 103
		}
	},
	{#State 92
		DEFAULT => -30
	},
	{#State 93
		ACTIONS => {
			'TOK_ELSE' => 105
		},
		DEFAULT => -61,
		GOTOS => {
			'elsepart' => 104
		}
	},
	{#State 94
		ACTIONS => {
			'NAME' => 10,
			'CONSTRMARKER' => 14,
			"[" => 20,
			'TOK_IF' => 15
		},
		DEFAULT => -63,
		GOTOS => {
			'feature' => 17,
			'statements' => 106,
			'stdvar' => 11,
			'assignment' => 13,
			'statement' => 94,
			'variable' => 12,
			'shortform' => 19,
			'condition' => 21
		}
	},
	{#State 95
		DEFAULT => -49
	},
	{#State 96
		ACTIONS => {
			"-" => 51,
			"<" => 52,
			"+" => 53,
			">=" => 54,
			"*" => 55,
			"<>" => 56,
			'TOK_IS' => 57,
			"&" => 58,
			"/" => 59,
			"=" => 60,
			'AND' => 61,
			'OR' => 62,
			"<=" => 63,
			"\\" => 64,
			">" => 65
		},
		DEFAULT => -53
	},
	{#State 97
		DEFAULT => -51
	},
	{#State 98
		ACTIONS => {
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			'CONST_FALSE' => 31,
			'CONST_STRING' => 39,
			"[" => 20
		},
		GOTOS => {
			'rangeexprlist' => 107,
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'constant' => 36,
			'simpleexpr' => 100
		}
	},
	{#State 99
		ACTIONS => {
			"}" => 108,
			"," => 109
		}
	},
	{#State 100
		ACTIONS => {
			"-" => 110
		},
		DEFAULT => -43
	},
	{#State 101
		ACTIONS => {
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			'CONST_FALSE' => 31,
			'CONST_STRING' => 39,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'simpleexprlist' => 111,
			'constant' => 36,
			'simpleexpr' => 83
		}
	},
	{#State 102
		ACTIONS => {
			"-" => 26,
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			"+" => 28,
			'FUNCNAME' => 35,
			'NOT' => 30,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			"(" => 38,
			'CONST_STRING' => 39,
			'CONST_FALSE' => 31,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'expression' => 89,
			'constant' => 36,
			'simpleexpr' => 40,
			'exprlist' => 112
		}
	},
	{#State 103
		DEFAULT => -33
	},
	{#State 104
		ACTIONS => {
			'TOK_ENDIF' => 113
		}
	},
	{#State 105
		ACTIONS => {
			'NAME' => 10,
			'CONSTRMARKER' => 14,
			"[" => 20,
			'TOK_IF' => 15
		},
		GOTOS => {
			'feature' => 17,
			'statements' => 114,
			'stdvar' => 11,
			'assignment' => 13,
			'statement' => 94,
			'variable' => 12,
			'shortform' => 19,
			'condition' => 21
		}
	},
	{#State 106
		DEFAULT => -64
	},
	{#State 107
		ACTIONS => {
			"}" => 115,
			"," => 109
		}
	},
	{#State 108
		DEFAULT => -32
	},
	{#State 109
		ACTIONS => {
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			'CONST_FALSE' => 31,
			'CONST_STRING' => 39,
			"[" => 20
		},
		GOTOS => {
			'rangeexprlist' => 116,
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'constant' => 36,
			'simpleexpr' => 100
		}
	},
	{#State 110
		ACTIONS => {
			'NAME' => 10,
			'CONST_TRUE' => 33,
			'CONST_LONG' => 34,
			'CONST_DOUBLE' => 29,
			'CONST_PI' => 37,
			'CONST_FALSE' => 31,
			'CONST_STRING' => 39,
			"[" => 20
		},
		GOTOS => {
			'feature' => 17,
			'stdvar' => 11,
			'variable' => 27,
			'constant' => 36,
			'simpleexpr' => 117
		}
	},
	{#State 111
		DEFAULT => -42
	},
	{#State 112
		DEFAULT => -40
	},
	{#State 113
		DEFAULT => -60
	},
	{#State 114
		DEFAULT => -62
	},
	{#State 115
		DEFAULT => -31
	},
	{#State 116
		ACTIONS => {
			"," => 109
		},
		DEFAULT => -45
	},
	{#State 117
		DEFAULT => -44
	}
],
                                  yyrules  =>
[
	[#Rule 0
		 '$start', 2, undef
	],
	[#Rule 1
		 'grammar', 2,
sub
#line 39 "ParserRules.yp"
{
			    new util::constraintparser::Constraint(
				$_[0]->YYData->{symtable}, $_[2]);
			}
	],
	[#Rule 2
		 'grammarstatements', 0,
sub
#line 46 "ParserRules.yp"
{
			    [];
			}
	],
	[#Rule 3
		 'grammarstatements', 2,
sub
#line 50 "ParserRules.yp"
{
			    unshift @{$_[2]},@{$_[1]};
			    $_[2];
			}
	],
	[#Rule 4
		 'vardecls', 0,
sub
#line 57 "ParserRules.yp"
{
			    undef;
			}
	],
	[#Rule 5
		 'vardecls', 2,
sub
#line 61 "ParserRules.yp"
{
			    undef;
			}
	],
	[#Rule 6
		 'vardecl', 3,
sub
#line 67 "ParserRules.yp"
{
			    my $sym = new util::constraintparser::Symbol(
				SYM_VARIABLE,
				$_[2],
				$_[1],
				$_[3]);
			    $sym->markReferenced;

			    unless ($_[0]->YYData->{symtable}->add($sym)) {
			        $_[0]->YYData->{errmsg} = 
				    "Variable '$_[2]' already declared";
				$_[0]->YYError;
			    }

			    undef;
			}
	],
	[#Rule 7
		 'datatype', 1,
sub
#line 86 "ParserRules.yp"
{
				DATATYPE_LONG;
			}
	],
	[#Rule 8
		 'datatype', 1,
sub
#line 90 "ParserRules.yp"
{
				DATATYPE_DOUBLE;
			}
	],
	[#Rule 9
		 'datatype', 1,
sub
#line 94 "ParserRules.yp"
{
				DATATYPE_STRING;
			}
	],
	[#Rule 10
		 'globaldecl', 0,
sub
#line 100 "ParserRules.yp"
{
			    undef;
			}
	],
	[#Rule 11
		 'globaldecl', 1,
sub
#line 104 "ParserRules.yp"
{
			    SYM_GLOBAL;
			}
	],
	[#Rule 12
		 'shortform', 2,
sub
#line 110 "ParserRules.yp"
{
			    my $table = $_[0]->YYData->{symtable};
			    my $sym = $table->findSymbol(SYM_VARIABLE,
			        "validate");
			    unless ($sym) {
			        $_[0]->YYData->{errmsg} = 
				    "Internal error; missing global " .
				    "variable 'validate'";
				$_[0]->YYError;
				return undef;
			    }
			    $sym->markAssigned;

			    new util::constraintparser::Condition(
				new util::constraintparser::Expression(
				    $_[2],
				    OPERATOR_NOT,
				    undef),
				[ new util::constraintparser::Expression(
				    new util::constraintparser::Expression(
					new util::constraintparser::Basic(
					      BASICTYPE_VARREF,
					      $sym),
					OPERATOR_BASICREF,
					undef),
				    OPERATOR_ASSIGN,
				    new util::constraintparser::Expression(
					new util::constraintparser::Basic(
					    BASICTYPE_STATIC,
					    DATATYPE_LONG,
					    0),
					OPERATOR_BASICREF,
					undef)
				    ) ],
				[]);
			}
	],
	[#Rule 13
		 'expression', 3,
sub
#line 149 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_ADD,
				$_[3]);
			}
	],
	[#Rule 14
		 'expression', 3,
sub
#line 156 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_SUB,
				$_[3]);
			}
	],
	[#Rule 15
		 'expression', 2,
sub
#line 163 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[2],
				OPERATOR_NEG);
			}
	],
	[#Rule 16
		 'expression', 2,
sub
#line 169 "ParserRules.yp"
{
				$_[2];
			}
	],
	[#Rule 17
		 'expression', 3,
sub
#line 173 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_REMAINDER,
				$_[3]);
			}
	],
	[#Rule 18
		 'expression', 3,
sub
#line 180 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_DIV,
				$_[3]);
			}
	],
	[#Rule 19
		 'expression', 3,
sub
#line 187 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_MULT,
				$_[3]);
			}
	],
	[#Rule 20
		 'expression', 3,
sub
#line 194 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_CONCAT,
				$_[3]);
			}
	],
	[#Rule 21
		 'expression', 3,
sub
#line 201 "ParserRules.yp"
{
			    if( @{$_[3]} > 1 ) {
				my $tmpexpr = undef;
				for my $expr ( @{$_[3]} ) {
				    my $tmp2 = new util::constraintparser::Expression(
					$_[1],
					util::constraintparser::Expression::OPERATOR_EQ,
					$expr);
				    if( $tmpexpr ) {
					$tmpexpr = new util::constraintparser::Expression(
					    $tmpexpr,
					    util::constraintparser::Expression::OPERATOR_OR,
					    $tmp2);
				    }
				    else {
					$tmpexpr = $tmp2;
				    }
				}
				$tmpexpr;
			    }
			    else {
				new util::constraintparser::Expression(
				    $_[1],
				    util::constraintparser::Expression::OPERATOR_EQ,
				    $_[3]->[0]);
			    }
			}
	],
	[#Rule 22
		 'expression', 3,
sub
#line 229 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_NE,
				$_[3]);
			}
	],
	[#Rule 23
		 'expression', 3,
sub
#line 236 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_GT,
				$_[3]);
			}
	],
	[#Rule 24
		 'expression', 3,
sub
#line 243 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_GE,
				$_[3]);
			}
	],
	[#Rule 25
		 'expression', 3,
sub
#line 250 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_LT,
				$_[3]);
			}
	],
	[#Rule 26
		 'expression', 3,
sub
#line 257 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_LE,
				$_[3]);
			}
	],
	[#Rule 27
		 'expression', 3,
sub
#line 264 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_AND,
				$_[3]);
			}
	],
	[#Rule 28
		 'expression', 3,
sub
#line 271 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_OR,
				$_[3]);
			}
	],
	[#Rule 29
		 'expression', 2,
sub
#line 278 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[2],
				OPERATOR_NOT,
				undef);
			}
	],
	[#Rule 30
		 'expression', 3,
sub
#line 285 "ParserRules.yp"
{
			    $_[2];
			}
	],
	[#Rule 31
		 'expression', 6,
sub
#line 289 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				is_expression($_[0], $_[1], $_[5]),
				OPERATOR_NOT,
				undef);
			}
	],
	[#Rule 32
		 'expression', 5,
sub
#line 296 "ParserRules.yp"
{
			    is_expression($_[0], $_[1], $_[4]);
			}
	],
	[#Rule 33
		 'expression', 4,
sub
#line 300 "ParserRules.yp"
{
			    my $table = $_[0]->YYData->{symtable};
			    my $sym = $table->findSymbol(SYM_FUNCTION, $_[1]);
			    unless ($sym) {
				$_[0]->YYData->{errmsg} = 
				    "Undefined function '$_[1]'";
				$_[0]->YYError;
				return undef;
			    }
			    $sym->markReferenced;

			    new util::constraintparser::Expression(
				new util::constraintparser::Basic(
					BASICTYPE_FUNCTION,
					$sym,
					$_[3]),
				OPERATOR_BASICREF,
				undef);
			}
	],
	[#Rule 34
		 'expression', 1,
sub
#line 320 "ParserRules.yp"
{
			    $_[1];
			}
	],
	[#Rule 35
		 'simpleexpr', 1,
sub
#line 326 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_BASICREF,
				undef);
			}
	],
	[#Rule 36
		 'simpleexpr', 1,
sub
#line 333 "ParserRules.yp"
{
			    new util::constraintparser::Expression(
				$_[1],
				OPERATOR_BASICREF,
				undef);
			}
	],
	[#Rule 37
		 'condexprlist', 0,
sub
#line 342 "ParserRules.yp"
{
			    [];
			}
	],
	[#Rule 38
		 'condexprlist', 1,
sub
#line 346 "ParserRules.yp"
{
			    $_[1];
			}
	],
	[#Rule 39
		 'exprlist', 1,
sub
#line 352 "ParserRules.yp"
{
			    [ $_[1] ];
			}
	],
	[#Rule 40
		 'exprlist', 3,
sub
#line 356 "ParserRules.yp"
{
			    unshift @{$_[3]}, $_[1];
			    $_[3];
			}
	],
	[#Rule 41
		 'simpleexprlist', 1,
sub
#line 363 "ParserRules.yp"
{
			    [ $_[1] ];
			}
	],
	[#Rule 42
		 'simpleexprlist', 3,
sub
#line 367 "ParserRules.yp"
{
			    unshift @{$_[3]}, $_[1];
			    $_[3];
			}
	],
	[#Rule 43
		 'rangeexprlist', 1,
sub
#line 374 "ParserRules.yp"
{
			    [ { top => $_[1] } ];
			}
	],
	[#Rule 44
		 'rangeexprlist', 3,
sub
#line 378 "ParserRules.yp"
{
			    [ { bottom => $_[1], top => $_[3] } ];
			}
	],
	[#Rule 45
		 'rangeexprlist', 3,
sub
#line 382 "ParserRules.yp"
{
			    unshift @{$_[3]}, $_[1]->[0];
			    $_[3];
			}
	],
	[#Rule 46
		 'variable', 1,
sub
#line 389 "ParserRules.yp"
{
			    $_[1];
			}
	],
	[#Rule 47
		 'variable', 1,
sub
#line 393 "ParserRules.yp"
{
			    $_[1];
			}
	],
	[#Rule 48
		 'stdvar', 2,
sub
#line 399 "ParserRules.yp"
{
			    my $table = $_[0]->YYData->{symtable};
			    my $sym = $table->findSymbol(SYM_VARIABLE, $_[1]);
			    unless ($sym) {
			        $_[0]->YYData->{errmsg} = 
				    "Undefined variable '$_[1]'";
			        $_[0]->YYError;
				return undef;
			    }
			    $sym->markReferenced;

			    if ($_[2]->[0] &&
				    $sym->datatype != DATATYPE_STRING) {
			        $_[0]->YYData->{errmsg} = 
				    "Variable '$_[1]' not a string";
			        $_[0]->YYError;
				return undef;
			    }

			    new util::constraintparser::Basic(
				    BASICTYPE_VARREF,
				    $sym,
				    $_[2]->[0],$_[2]->[1]),
			}
	],
	[#Rule 49
		 'feature', 4,
sub
#line 426 "ParserRules.yp"
{
			    my $table = $_[0]->YYData->{symtable};
			    my $sym = $table->findSymbol(SYM_FEATURE, $_[2]);
			    unless ($sym) {
			        # Lookup the feature type
				my $erp = $_[0]->YYData->{erp};

				my $res = $erp->request("FEATURE_GENERAL_DATA",
				    { feature => $_[2] } );
				unless ($res) {
				    $_[0]->YYData->{errmsg} =
				        "Error with ERP Adapter request";
				    $_[0]->YYError;
				    return undef;
				}
				unless ($res->[0]{featureid}) {
				    $_[0]->YYData->{errmsg} =
				        "Invalid feature '$_[2]'";
				    $_[0]->YYError;
				    return undef;
				}

				my $datatype;
				if ($res->[0]{valuedomain} == 1) { 
				    $datatype = DATATYPE_STRING;
				}
				elsif ($res->[0]{valuedomain} == 2) { 
				    $datatype = DATATYPE_LONG;
				}
				elsif ($res->[0]{valuedomain} == 3) {
				    $datatype = DATATYPE_DOUBLE;
				}
				else {
				    $_[0]->YYData->{errmsg} =
				        "Feature '$_[2]' has unknown type";
				    $_[0]->YYError;
				    return undef;
				}

			        $sym = new util::constraintparser::Symbol(
				    SYM_FEATURE,
				    $_[2],
				    $datatype);
				$sym->markReferenced;
				$table->add($sym);
			    }

			    if ($_[4]->[0] &&
				    $sym->datatype != DATATYPE_STRING) {
			        $_[0]->YYData->{errmsg} = 
				    "Feature '$_[2]' not a string";
			        $_[0]->YYError;
				return undef;
			    }

			    new util::constraintparser::Basic(
				BASICTYPE_VARREF, $sym,
				$_[4]->[0], $_[4]->[1]);
			}
	],
	[#Rule 50
		 'subscript', 0,
sub
#line 488 "ParserRules.yp"
{
			    [ undef, undef ];
			}
	],
	[#Rule 51
		 'subscript', 4,
sub
#line 492 "ParserRules.yp"
{
			    [ $_[2], $_[3] ];
			}
	],
	[#Rule 52
		 'subscriptleng', 0,
sub
#line 498 "ParserRules.yp"
{
			    undef;
			}
	],
	[#Rule 53
		 'subscriptleng', 2,
sub
#line 502 "ParserRules.yp"
{
			    $_[2];
			}
	],
	[#Rule 54
		 'constant', 1,
sub
#line 508 "ParserRules.yp"
{
			    new util::constraintparser::Basic(
				    BASICTYPE_STATIC,
				    DATATYPE_STRING,
				    $_[1]);
			}
	],
	[#Rule 55
		 'constant', 1,
sub
#line 515 "ParserRules.yp"
{
			    new util::constraintparser::Basic(
				    BASICTYPE_STATIC,
				    DATATYPE_LONG,
				    $_[1]);
			}
	],
	[#Rule 56
		 'constant', 1,
sub
#line 522 "ParserRules.yp"
{
			    new util::constraintparser::Basic(
				    BASICTYPE_STATIC,
				    DATATYPE_DOUBLE,
				    $_[1]);
			}
	],
	[#Rule 57
		 'constant', 1,
sub
#line 529 "ParserRules.yp"
{
			    new util::constraintparser::Basic(
				    BASICTYPE_STATIC,
				    DATATYPE_LONG,
				    1);
			}
	],
	[#Rule 58
		 'constant', 1,
sub
#line 536 "ParserRules.yp"
{
			    new util::constraintparser::Basic(
				    BASICTYPE_STATIC,
				    DATATYPE_LONG,
				    0);
			}
	],
	[#Rule 59
		 'constant', 1,
sub
#line 543 "ParserRules.yp"
{
			    new util::constraintparser::Basic(
				    BASICTYPE_STATIC,
				    DATATYPE_DOUBLE,
				    3.14159265358979323846);
			}
	],
	[#Rule 60
		 'condition', 6,
sub
#line 552 "ParserRules.yp"
{
			    new util::constraintparser::Condition(
				$_[2],$_[4],$_[5]);
			}
	],
	[#Rule 61
		 'elsepart', 0,
sub
#line 559 "ParserRules.yp"
{
			    [];
			}
	],
	[#Rule 62
		 'elsepart', 2,
sub
#line 563 "ParserRules.yp"
{
			    $_[2];
			}
	],
	[#Rule 63
		 'statements', 1,
sub
#line 569 "ParserRules.yp"
{
			    $_[1];
			}
	],
	[#Rule 64
		 'statements', 2,
sub
#line 573 "ParserRules.yp"
{
			    push @{$_[1]}, @{$_[2]};
			    $_[1];
			}
	],
	[#Rule 65
		 'statement', 1,
sub
#line 579 "ParserRules.yp"
{
			    [ $_[1] ];
			}
	],
	[#Rule 66
		 'statement', 1,
sub
#line 583 "ParserRules.yp"
{
			    [ $_[1] ];
			}
	],
	[#Rule 67
		 'statement', 1,
sub
#line 587 "ParserRules.yp"
{
			    [ $_[1] ];
			}
	],
	[#Rule 68
		 'assignment', 3,
sub
#line 593 "ParserRules.yp"
{
			    $_[1]->{symbol}->markAssigned;

			    new util::constraintparser::Expression(
				new util::constraintparser::Expression(
				    $_[1],
				    OPERATOR_BASICREF,
				    undef),
				OPERATOR_ASSIGN,
				$_[3]);
			}
	]
],
                                  @_);
    bless($self,$class);
}

#line 605 "ParserRules.yp"


sub is_expression {
    my ($yyparser, $leftexpr, $exprlist) = @_;
    my $tmpexpr = undef;
    my $tmp2;
    for my $expr ( @{$exprlist} ) {
	if( $expr->{bottom} ) {
	    $tmp2 = new util::constraintparser::Expression(
		new util::constraintparser::Expression(
		    $leftexpr,
		    util::constraintparser::Expression::OPERATOR_GE,
		    $expr->{bottom}),
		util::constraintparser::Expression::OPERATOR_AND,
		new util::constraintparser::Expression(
		    $leftexpr,
		    util::constraintparser::Expression::OPERATOR_LE,
		    $expr->{top}) );
	}
	else {
	    $tmp2 = new util::constraintparser::Expression(
		$leftexpr,
		util::constraintparser::Expression::OPERATOR_EQ,
		$expr->{top});
	}
	if( $tmpexpr ) {
	    $tmpexpr = new util::constraintparser::Expression(
		$tmpexpr,
		util::constraintparser::Expression::OPERATOR_OR,
		$tmp2);
	}
	else {
	    $tmpexpr = $tmp2;
	}
    }
    $tmpexpr;
}

1;
