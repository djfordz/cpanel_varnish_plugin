package Cpanel::API::NemjVarnish;

use Cpanel                  ();
use Cpanel::AdminBin::Call  ();
use Cpanel::LoadModule      ();
use Data::Dumper            ();

sub write {
    my ( $args, $result ) = @_;

    my $path = $args->get('path');
    my $data = $args->get('data');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'WRITE',
        $path,
	    $data,
    );

    return $result->data($val);
}

sub overwrite {
    my ( $args, $result ) = @_;

    my $path = $args->get('path');
    my $data = $args->get('data');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'OVERWRITE',
        $path,
	    $data,
    );

    return $result->data($val);
}

sub read {
    my ( $args, $result ) = @_;
    my $path = $args->get('path');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'READ',
        $path,
    );

    return $result->data($val);
}

sub start {
    my ( $args, $result ) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'START',
        $program,
    );

    return $result->data($val);
}

sub stop {
    my ( $args, $result ) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'STOP',
        $program,
    );

    return $result->data($val);
}

sub restart {
    my ( $args, $result ) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'RESTART',
        $program,
    );

    return $result->data($val);
}

sub reload {
    my ( $args, $result ) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'RELOAD',
        $program,
    );

    return $result->data($val);
}

sub start_varnish {
    my ($args, $result) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'START_VARNISH',
        $program,
    );

    return $result->data($val);
}

sub stop_varnish {
    my ($args, $result) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'STOP_VARNISH',
        $program,
    );

    return $result->data($val);
}

sub restart_varnish {
    my ($args, $result) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'RESTART_VARNISH',
        $program,
    );

    return $result->data($val);
}

sub reload_varnish {
    my ( $args, $result ) = @_;
    my $program = $args->get('program');
    my $val;

    $val = Cpanel::AdminBin::Call::call(
        'Nemanja',
        'Varnish',
        'RELOAD_VARNISH',
        $program,
    );

    return $result->data($val);
}

1;
