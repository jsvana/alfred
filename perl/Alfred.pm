#!/usr/bin/perl

package Alfred;

use LWP::UserAgent;
use Data::Dumper;
use JSON;

sub new {
  my $class = shift;
  my $self = {
      _apiKey => "",
  };

  bless $self, $class;

  return $self;
}

sub setAPIKey {
  my ($self, $apiKey) = @_;

  $self->{_apiKey} = $apiKey if defined($apiKey);
  return $self->{_apiKey};
}

sub getAPIKey {
  my ($self) = @_;
  return $self->{_apiKey};
}

sub login {
  my ($self, $username, $password) = @_;

  my $params = {
    "username" => $username,
    "password" => $password,
  };

  my $data = $self->request("Alfred.Login", $params);

  if($data->{'code'} >= 0) {
    $self->setAPIKey($data->{'data'}->{'key'});
  }
}

sub request {
  my ($self, $method, $args) = @_;

  my $data = '{"alfred":"0.1","key":"' . $self->getAPIKey() . '","method":"' . $method . '","params":{';

  if(defined($args)) {
    for my $key ( keys %$args ) {
      my $value = $args->{$key};
      $data .= '"' . $key . '":"' . $value . '",';
    }

    chop($data);
  }

  $data .= "}}";

  my $ua = new LWP::UserAgent;

  my $req = HTTP::Request->new(POST => 'http://alf.re/d/');
  $req->content_type('application/x-www-form-urlencoded');
  $req->content($data);

  my $res = $ua->request($req);
  my $content = $res->content;

  my $json = decode_json($content);
  my $ret;

  $ret->{'code'} = $json->{'code'};
  $ret->{'data'} = $json->{'data'};

  return $ret;
}

1;
