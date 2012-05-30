//
//  AppController.m
//  Alfred
//
//  Created by Jay Vana on 5.24.12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import "AppController.h"
#import "SBJson.h"

@implementation AppController
id playerID;

- (IBAction)changeText:(id)sender {
    NSString *post = [NSString stringWithFormat:@"json=&master=%@&site=%@", [master stringValue], [site stringValue]];
    NSData *postData = [post dataUsingEncoding:NSASCIIStringEncoding allowLossyConversion:YES];
    
    NSString *postLength = [NSString stringWithFormat:@"%d", [postData length]];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] init];
    [request setURL:[NSURL URLWithString:@"http://psg.mtu.edu:21516/alfred/passwords/get.php"]];
    [request setHTTPMethod:@"POST"];
    [request setValue:postLength forHTTPHeaderField:@"Content-Length"];
    [request setValue:@"application/x-www-form-urlencoded" forHTTPHeaderField:@"Content-Type"];
    [request setHTTPBody:postData];
    
    // Create new SBJSON parser object
    SBJsonParser *parser = [[SBJsonParser alloc] init];
    
    // Perform request and get JSON back as a NSData object
    NSData *response = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
    
    NSString *json_string = [[NSString alloc] initWithData:response encoding:NSUTF8StringEncoding];
    
    NSDictionary *ret = [parser objectWithString:json_string error:nil];
    
    if(![[ret objectForKey:@"error"] isEqualToString:@""]) {
        [label setStringValue:[NSString stringWithFormat:@"Error: %@", [ret objectForKey:@"error"]]];
    } else {
        [label setStringValue:[NSString stringWithFormat:@"Password: %@", [ret objectForKey:@"password"]]];
    }
}

- (IBAction)clearLabel:(id)sender {
    [label setStringValue:@""];
}

- (IBAction)addPassword:(id)sender {
    NSString *post = [NSString stringWithFormat:@"json=&master=%@&new=%@&site=%@", [masterAdd stringValue], [newPassAdd stringValue], [siteAdd stringValue]];
    NSData *postData = [post dataUsingEncoding:NSASCIIStringEncoding allowLossyConversion:YES];
    
    NSString *postLength = [NSString stringWithFormat:@"%d", [postData length]];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] init];
    [request setURL:[NSURL URLWithString:@"http://psg.mtu.edu:21516/alfred/passwords/add.php"]];
    [request setHTTPMethod:@"POST"];
    [request setValue:postLength forHTTPHeaderField:@"Content-Length"];
    [request setValue:@"application/x-www-form-urlencoded" forHTTPHeaderField:@"Content-Type"];
    [request setHTTPBody:postData];
    
    // Create new SBJSON parser object
    SBJsonParser *parser = [[SBJsonParser alloc] init];
    
    // Perform request and get JSON back as a NSData object
    NSData *response = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
    
    NSString *json_string = [[NSString alloc] initWithData:response encoding:NSUTF8StringEncoding];
    
    NSDictionary *ret = [parser objectWithString:json_string error:nil];
    
    if(![[ret objectForKey:@"error"] isEqualToString:@""]) {
        [labelAdd setStringValue:[NSString stringWithFormat:@"Error: %@", [ret objectForKey:@"error"]]];
    } else {
        [labelAdd setStringValue:[NSString stringWithFormat:@"Password added successfully."]];
    }
}

- (IBAction)clearAddLabel:(id)sender {
    [labelAdd setStringValue:@""];
}

- (IBAction)xbmcTest:(id)sender {
    NSString *jsonString = @"{\"jsonrpc\": \"2.0\", \"method\": \"Player.GetActivePlayers\", \"params\": { }, \"id\": 1}";
    
    NSData *requestData = [NSData dataWithBytes: [jsonString UTF8String] length: [jsonString length]];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL: [NSURL URLWithString:@"http://xbmc:1123581321!@tananda.bangarang.com:8080/jsonrpc"]];
    
    [request setHTTPMethod: @"POST"];
    [request setValue:@"Content-type: application/json" forHTTPHeaderField:@"Content-Type"];
    [request setHTTPBody:requestData];
    
    NSData *response = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
    
    NSString *json_string = [[NSString alloc] initWithData:response encoding:NSUTF8StringEncoding];
    NSLog(@"ret: %@", json_string);
    
    SBJsonParser *parser = [[SBJsonParser alloc] init];
    
    NSDictionary *ret = [parser objectWithString:json_string error:nil];
    
    NSArray *players = [ret objectForKey:@"result"];
    playerID = [[players objectAtIndex:0] objectForKey:@"playerid"];
    
    jsonString = [NSString stringWithFormat:@"{\"jsonrpc\": \"2.0\", \"method\": \"Player.PlayPause\", \"params\": { \"playerid\": %@ }, \"id\": 1}", playerID];
    
    NSLog(@"str: %@", jsonString);
    
    requestData = [NSData dataWithBytes: [jsonString UTF8String] length: [jsonString length]];
    
    request = [[NSMutableURLRequest alloc] initWithURL: [NSURL URLWithString:@"http://xbmc:1123581321!@tananda.bangarang.com:8080/jsonrpc"]];
    
    [request setHTTPMethod: @"POST"];
    [request setValue:@"Content-type: application/json" forHTTPHeaderField:@"Content-Type"];
    [request setHTTPBody:requestData];
    
    response = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
    
    NSLog(@"res: %@", [[NSString alloc] initWithData:response encoding:NSUTF8StringEncoding]);
}

- (IBAction)getPlayingSong:(id)sender {
    NSString *jsonString = [NSString stringWithFormat:@"{\"jsonrpc\": \"2.0\", \"method\": \"Player.GetItem\", \"params\": { \"playerid\": %@ }, \"id\": 1}", playerID];
    
    NSData *requestData = [NSData dataWithBytes: [jsonString UTF8String] length: [jsonString length]];
    
    NSMutableURLRequest *request = [[NSMutableURLRequest alloc] initWithURL: [NSURL URLWithString:@"http://xbmc:1123581321!@tananda.bangarang.com:8080/jsonrpc"]];
    
    [request setHTTPMethod: @"POST"];
    [request setValue:@"Content-type: application/json" forHTTPHeaderField:@"Content-Type"];
    [request setHTTPBody:requestData];
    
    NSData *response = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
    
    NSString *json_string = [[NSString alloc] initWithData:response encoding:NSUTF8StringEncoding];
    
    SBJsonParser *parser = [[SBJsonParser alloc] init];
    
    NSDictionary *ret = [parser objectWithString:json_string error:nil];
    NSDictionary *result = [ret objectForKey:@"result"];
    NSDictionary *item = [result objectForKey:@"item"];
    [trackInfo setStringValue:[item objectForKey:@"label"]];
}

@end
