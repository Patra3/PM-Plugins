#PushbulletPM  
Send pushes using Pushbullet, easily!  

### What is Pushbullet?  
Pushbullet is a 3rd party notifications/messaging program that allows all of your device(s) to be connected.  
You can deliever notifications to all your devices, and it's a very good service.  
More info: https://www.pushbullet.com/   

### Well, what does this plugin do?  
Simply stated, this plugin allows for integration with your Pushbullet account, so you can recieve some basic   
notifications. I am planning on adding updates to this plugin very soon, with more pushing features!  

### How do I set this up?  
Watch my video tutorial:  
https://www.youtube.com/watch?v=JzkbQYnhj-s&feature=youtu.be

### For Developers:  
Now, I know you are likely anxious to get your hands on this for push notifications.  
Luckily, I made three (currently) unique methods you can call.  

In the core of my plugin, you can use sendPush(), with the following parameters:  
token key, title, message, recieving user email  

```
$pushbulletclass->sendPush($client_api_key, $push_title, $push_message, $recieving_user_email);
```  

and getPushbulletUser() to check if the user is a registered user:
token key  

```
$pushbulletclass->getPushbulletUser($client_api_key);
```  

Be wary, the second method returns a user object directly from Pushbullet servers.  

NOTE: Since update 1.1.0, sendPush() now uses an efficient AsyncTask. If you want the old method (now deprecated),
use directPush() with the same parameters as sendPush().  

### What do you plan to add?  
Everything seems to have been done. Working on slight fixes and tweaks as needed, but nothing else for now...  

### Why is this plugin connecting to server?  
The plugin connects to an online server because pushses can't be curl()'d directly  
from PocketMine servers, and for verification reasons.