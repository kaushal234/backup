

use strict;

package util::constraintparser::Lexer;

use util::constraintparser::Symbol;

sub new
{
	my ($this,$symtable,$text) = @_;
	my $class = ref($this) || $this;
	my $self = {
	    text => $text,
	    currpos => 0,
	    currline => 1,
	    returneol => 0,
	    debug => 0,
	    startofline => 1,
	    symtable => $symtable,
	    keywords => {
		true	=> { name => "CONST_TRUE" },
		false	=> { name => "CONST_FALSE" },
		pi	=> { name => "CONST_PI" },
		if	=> { name => "TOK_IF" },
		then	=> { name => "TOK_THEN" },
		else	=> { name => "TOK_ELSE" },
		endif	=> { name => "TOK_ENDIF" },
		and	=> { name => "AND" },
		or	=> { name => "OR" },
		not	=> { name => "NOT" },
		is	=> { name => "TOK_IS" },

		long	=> { name => "TOK_LONG" },
		string	=> { name => "TOK_STRING" },
		double	=> { name => "TOK_DOUBLE" },
		global	=> { name => "TOK_GLOBAL" },
	    },
	};

	bless $self,$class;
	return $self;
}

sub getCurrLine
{
	my $self = shift;
	return $self->{currline};
}

sub getLineText
{
	my $self = shift;
	my ($start, $end);

	$start = rindex($self->{text}, "\n", $self->{currpos} - 1);
	$start++;
	$end   = index($self->{text}, "\n", $start);
	$end   = length($self->{text}) + 1 if ($end < 0);
	return substr($self->{text}, $start, $end - $start);
}

sub getLinePos
{
	my $self = shift;
	my ($start);

	$start = rindex($self->{text}, "\n", $self->{currpos} - 1);
	$start++;
	return $self->{currpos} - $start - 1;
}

sub nextToken
{
	my $self = shift;

	if( length($self->{text}) <= $self->{currpos} ) {
		print STDERR "EOF reached\n" if $self->{debug};
		return ('',undef);
	}
	my $token = substr($self->{text},$self->{currpos});
	while(1) {
		#
		# remove lines that start with '!'
		#
		if( $self->{startofline} && ($token =~ /^\!/) ) {
			while( $token !~ /^\n/ ) {
				$self->{currpos}++;
				$token = substr($token,1);
			}
			$self->{currpos}++;
			$token = substr($token,1);
			next;
		}
		#
		# remove leading whitespace
		#
		if ($token =~ /^([ \t]+)(.*)/s) {
			$self->{currpos} += length($1);
			$token = $2;
			$self->{startofline} = 0;
		}
		#
		# check, if we have to consider the "NL" as a token
		#
		if( substr($token,0,1) eq "\n" ) {
			$self->{currpos}++;
			$self->{currline}++;
			$token = substr($token,1);
			$self->{startofline} = 1;
			if( $self->{returneol} ) {
				print STDERR "returning EOL\n" if $self->{debug};
				return ('TOK_EOL','');
			}
			next;
		}
		#
		# remove comments
		#
		if( $token =~ /^\|/ ) {
			while( $token !~ /^\n/ ) {
				$self->{currpos}++;
				$token = substr($token,1);
			}
			#
			# if the comment was not for the entire line,
			# we eventually have to consider the new line character
			#
			next unless( $self->{startofline} );
			$self->{currpos}++;
			$self->{currline}++;
			$token = substr($token,1);
			next;
		}
		last;
	}
	unless( length($token) ) {
		print STDERR "returning EOF\n" if $self->{debug};
		return ('',undef);
	}
	#
	# is it one of these 2 char operators ?
	#
	if( $token =~ /^(\<\>|\<=|\>=)/ ) {
		$self->{startofline} = 0;
		$self->{currpos} += 2;
		print STDERR "returning <$1>\n" if $self->{debug};
		return ($1,$1);
	}
	#
	# string ?
	#
	if( $token =~ /^\"([^\"]*)\"/ ) {
		$self->{startofline} = 0;
		$self->{currpos} += length($1) + 2;
		print STDERR "returning CONST_STRING: <$1>\n" if $self->{debug};
		return ('CONST_STRING',$1);
	}
	#
	# constraint marker at start of line ?
	#
	if( $self->{startofline} && ($token =~ /^c:/) ) {
		$self->{startofline} = 0;
		$self->{currpos} += 2;
		print STDERR "returning CONSTRMARKER: <c:>\n" if $self->{debug};
		return ('CONSTRMARKER','c:');
	}
	if( ($token =~ /^(\d*\.\d+)[^\d\w]/) ) {
		$self->{startofline} = 0;
		$self->{currpos} += length($1);
		print STDERR "returning CONST_DOUBLE: <$1>\n" if $self->{debug};
		return ('CONST_DOUBLE',$1);
	}
	if( $token =~ /^(\d+)[^\d\w]/ ) {
		$self->{startofline} = 0;
		$self->{currpos} += length($1);
		print STDERR "returning CONST_LONG: <$1>\n" if $self->{debug};
		return ('CONST_LONG',$1);
	}
	unless( $token =~ /^([\d\w_]+)/ ) {
		$self->{startofline} = 0;
		$self->{currpos}++;
		print STDERR "returning <", substr($token,0,1), ">\n"
				if $self->{debug};
		return (substr($token,0,1),substr($token,0,1));
	}
	$self->{startofline} = 0;
	my $newtok = $1;
	$self->{currpos} += length($newtok);

	if( $self->{keywords}{lc $newtok} ) {
	    print STDERR "returning <", 
		$self->{keywords}{lc $newtok}{name}, ">: <",
		$newtok, ">\n" if $self->{debug};
	    return ($self->{keywords}{lc $newtok}{name},$newtok);
	}

	# FIXME: There will still be a problem when taking a subscript
	# of a string variable that looks like a function name; Baan
	# also would fail?
	if (substr($self->{text}, $self->{currpos}) =~ m/^\s*\(/ ) {
	    if ($self->{symtable}->findSymbol(SYM_FUNCTION, $newtok)) {
		print STDERR "return <FUNCNAME>: <", $newtok, ">\n" 
			if $self->{debug};
		return ('FUNCNAME', $newtok);
	    }
	}

	print STDERR "returning NAME: <$newtok>\n" if $self->{debug};
	return ('NAME',$newtok);
}

1;
