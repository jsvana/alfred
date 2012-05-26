//
//  AppController.h
//  Alfred
//
//  Created by Jay Vana on 5.24.12.
//  Copyright (c) 2012 __MyCompanyName__. All rights reserved.
//

#import <Foundation/Foundation.h>

@interface AppController : NSObject {
@private
    IBOutlet NSTextField *label;
    IBOutlet NSSecureTextField *master;
    IBOutlet NSTextField *site;
    
    IBOutlet NSTextField *labelAdd;
    IBOutlet NSSecureTextField *masterAdd;
    IBOutlet NSTextField *newPassAdd;
    IBOutlet NSTextField *siteAdd;
    
    IBOutlet NSTextField *trackInfo;
}

- (IBAction)changeText:(id)sender;
- (IBAction)clearLabel:(id)sender;

- (IBAction)addPassword:(id)sender;
- (IBAction)clearAddLabel:(id)sender;

- (IBAction)xbmcTest:(id)sender;
- (IBAction)getPlayingSong:(id)sender;

@end
