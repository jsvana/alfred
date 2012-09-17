#!/usr/bin/perl

use Alfred;
use Data::Dumper;

$alfred = new Alfred();

my $data;
$data->{'username'} = 'guest';
$data->{'password'} = 'hunter2';
my $method = "Alfred.Login";

$alfred->login("guest", "hunter2");

my $data = $alfred->request("MTU.WMTU");

print Dumper $data;
