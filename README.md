i3WebManager
============

Summary
=======
i3WebManager has a simple purpose. Help you start your day more quickly by starting all the app you need.

Purpose of i3WebManager
=======================
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
=======
To install, just do :
```bash
git clone https://github.com/bacardi55/i3WebManager/ && cd i3WebManager && git checkout 0.3.1-alpha
curl -s http://getcomposer.org/installer | php
php composer.phar install
```
Then create a vhost and set the root directory to the web directory.
Change the permissions to let the app create/modify a file in
```
src/b55/Resources/i3Config.yml
```

After creating your config file, you need to launch to i3CliManager. This console will launch your app in your workspace.
To launch i3CliManager,
```
php console i3CliManager:start [config_name]
```
with [config_name] the name of the config you want to launch.

Current State
=============
**Tag 0.3.1-alpha**: (**Don't use the 0.3-alpha, there is a fatal error)

New feature: Default layout per workspace.
Thanks to this, you can choose a layout for your workspace. When selecting a layout
the app will make sure that the choosen layout will be set for the workspace.
(You can not create split or layout in layout yet).

**Tag 0.2-alpha**:

You can now install the app, run it and create a new configuration from scratch.
For now, you can only add client to workspace. You can't do split or layout for now, but you'll be able to one day…
Plus, the console is also ready. So you can create your config in your web browser, and then run this command line :
```
php console i3CliManager:start test
```
You can't start it in a tty though, your i3session must be started.

**Tag 0.1** :
The app is currently useless. The load/saving part work though and that's the most important part.

FAQ
===
**Why PHP**:
I know that most of the i3 users wouldn't have made it in PHP but here are some of my reasons :
  - I'm a PHP developer;
  - I wanted to create my first app with the Silex micro framework;
  - The first version needed to be done in less than 3 days (to use it at my 1st 2013 job day ^^)

**Why YAML**:
I choose to save the config file in YAML because I didn't want a database for this, even sqlite. It was either
yaml or json. I like yaml more as it's more readable. In any case, if you want to use the config file from your own app, it should be easy enough.

Wish list
=========
What I want from this app (at least) :
- First :
  - Create multiple config (home, offices, …) (done)
  - Add client per workspace (done)
  - Add Scratchpad
  - Add client in specific split / layout
  - A cli php script to add at the start of i3 to launch your choosen configuration (done)
- Then :
  - Pre-configure i3WebManager by reading i3/config file
  - Drag & drop UI to create your i3 session as wanted
  - An export of the configuration in a bash script to not have php installed on the user pc.
  - Creating a configuration by reading your current i3Session
