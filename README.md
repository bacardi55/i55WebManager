i55WebManager
============

Summary
----
i55WebManager has a simple purpose. Help you start your day more quickly by starting all the app you need.

Purpose of i55WebManager
----
Have you ever start your laptop and thinking about all the app you need to open ?
I open the same apps every day at work and put my clients in order in all my workspace at the start of my day (I don't hibernate a lot because I need to boot windows to play ^^').
Even if I do it all thanks to the keybinds, it's boring me to do it every day.

The simple solution would be to use the build in system in i3 that let you launch app from the i3/config file. In this file, you can even assigned client to specific workspace.
But I didn't manage to make it work properly and it doesn't fit all my need because depending on where I use my laptop, i need some app or some other.

This is why i started this web application.
This app is a web interface that let you choose which application will open in which workspace.

In more details, this app handles as many configuration as you want. In each configuration, you will define the workspace that you use in your configuration.
Then, in each workspace, you will be able to add application to launch in it.

When you're finished with the configuration, this app has a cli php app that will run your configuration through i3-msg by going to the wanted workspace and openning clients in them.

This application will be able to handle all layouts and {h,v}split in the future, for now, only assigning a workspace to a client and set a default layout per workspace.


Install
----
To install, just do :
```bash
git clone https://github.com/bacardi55/i55WebManager/ && cd i55WebManager && git checkout 0.3.1-alpha
curl -s http://getcomposer.org/installer | php
php composer.phar install
```
Then create a vhost and set the root directory to the web directory.
Change the permissions to let the app create/modify a file in
```
src/b55/Resources/i3Config.yml
```

After creating your config file, you need to launch to i55CliManager. This console will launch your app in your workspace.
To launch i55CliManager,
```
php console i55CliManager:start [config_name]
```
with [config_name] the name of the config you want to launch.

Current State
----

FAQ
----
**Why PHP**:
I know that most of the i3 users wouldn't have made it in PHP but here are some of my reasons :
  - I'm a PHP developer;
  - I wanted to create my first app with the Silex micro framework;
  - The first version needed to be done in less than 3 days (to use it at my 1st 2013 job day ^^)

**Why YAML**:
I choose to save the config file in YAML because I didn't want a database for this, even sqlite. It was either
yaml or json. I like yaml more as it's more readable. In any case, if you want to use the config file from your own app, it should be easy enough.

Wish list
----
What I want from this app (at least) :
- First :
  - Create multiple config (home, offices, …) (done)
  - Add default layout per workspace (done)
  - Add client per workspace (done)
  - Add Scratchpad (done in web app but not when running config yet)
  - A cli php script to add at the start of i3 to launch your choosen configuration (done)
  - An export of the configuration in a bash script to not have php installed on the user pc. (done)
  - Creating a configuration by reading your current i3Session (done)
- Then :
  - Add client in specific split / layout !!!! :)
  - Pre-configure i55WebManager by reading i3/config file
  - Drag & drop UI to create your i3 session as wanted
