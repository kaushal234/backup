# *****************************************************************************
# Author  : Bernd Loske
# Date    : June 16, 2002
# Comments:
#   This script is part of a utility to parse Baan constraints and
#	generate programs in other languages
#
# $Id: Functions.pm,v 2.1 2003/05/01 05:53:33 kbrock Exp $
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

package configurator::Functions;

use Exporter;
use vars qw(@ISA @EXPORT);
@ISA = qw(Exporter);

@EXPORT = qw(util_int util_val util_min util_max util_pow util_tan
	     util_asin util_acos util_sinh util_cosh util_tanh util_asinh
    	     util_acosh util_atanh util_log10 util_str util_len util_strip
	     util_round util_substr set_substr util_edit util_date
	    );

sub util_int {
	my ($x) = @_;
	return sprintf("%d", $x);
}
sub util_val {
	return shift;
}
sub util_min {
	my ($x,$y) = @_;
	return ($x<$y)?$x:$y;
}
sub util_max {
	my ($x,$y) = @_;
	return ($x>$y)?$x:$y;
}
sub util_pow {
	my ($x,$y) = @_;
	return $x**$y;
}
sub util_tan {
	my ($x) = @_;
	return sin($x)/cos($x);
}
sub util_asin {
	my ($x) = @_;
	return 1/sin($x);
}
sub util_acos {
	my ($x) = @_;
	return 1/cos($x);
}
sub util_sinh {
	my ($x) = @_;
	return (exp($x)-exp(-$x))/2;
}
sub util_cosh {
	my ($x) = @_;
	return (exp($x)+exp(-$x))/2;
}
sub util_tanh {
	my ($x) = @_;
	return sinh($x)/cosh($x);
}
sub util_asinh {
	my ($x) = @_;
	return log($x+sqrt($x**2+1));
}
sub util_acosh {
	my ($x) = @_;
	return log($x+sqrt($x**2-1));
}
sub util_atanh {
	my ($x) = @_;
	return log((1+$x)/(1-$x))/2;
}
sub util_log10 {
	my ($x) = @_;
	return log($x)/log(10);
}
sub util_str {
	my ($x) = @_;
	return $x;
}
sub util_len {
	my ($x) = @_;
	return length($x);
}
sub util_strip {
	my ($x) = @_;
	my $y;
	($y = $x) =~ s/ +$//;
	return $y;
}
sub util_round {
        my ($x, $decimal, $mode) = @_;

	my $shift = 10**$decimal;
	if ($mode == 0) {		# Round down
	    return sprintf("%.0f", $x * $shift - 0.49999999) / $shift;
	} elsif ($mode == 2) {		# Round up
	    return sprintf("%.0f", $x * $shift + 0.49999999) / $shift;
	} else {			# Round "normal"
	    return sprintf("%.0f", $x * $shift + 0.00000001) / $shift;
	}
}
sub util_substr {
	my ($s, $start, $len) = @_;
	$len = length $s unless $len;
	return substr($s, $start - 1, $len) || "";
}
sub set_substr {
	my ($sref, $newval, $start, $len) = @_;
	$len = length $$sref unless $len;
	substr($$sref, $start - 1, $len) = $newval;
}

sub util_edit {
	my ($val, $mask) = @_;
	die "edit() function not yet implemented";
}

sub util_date {
	die "date() and date(y,m,d) functions not yet implemented";
}

1;
