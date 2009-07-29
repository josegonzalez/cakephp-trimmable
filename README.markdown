Trim Behavior neatly turns the current resource into a trim, tinyurl, or agd url

## Background
On a recent project, I needed to quickly tweet some information whenever I updated a post. The tweet required a link to the post as well, and thusly the idea for this behavior was born.

The behavior depends upon a.gd, tinyurl, or tr.im . You can also specify your own special api, but the entire thing simply does a file_get_contents(), so be warned. YMMV.

## Requirements
A server that allows you to use file_get_contents()

## Installation
- Clone from github : in your plugin directory type `git clone git://github.com/josegonzalez/trim-behavior.git trim`
- Add as a git submodule : in your plugin directory type `git submodule add git://github.com/josegonzalez/trim-behavior.git trim`
- Download an archive from github and extract it in `/plugins/trim`

## Usage
2. In the model that needs to be trimmed, add :
	var $actsAs = array('Trim');

At this point, everything should theoretically work. Note that by default, it only creates the trimmed url on creation of the record. This means it triggers a "saveField()" call, so be warned that this may screw with other behaviors.

## TODO:
1. Better code commenting
2. Remove the file_get_contents() dependency (perhaps a datasource of some sort)
3. Show how to set different APIs for the behavior in this readme
4. Fix the behavior so that the auto setting really does work.