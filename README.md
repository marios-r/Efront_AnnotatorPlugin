# Efront_AnnotatorPlugin
A tool to enhanche personalized learning on eFront LMS.  It's a text annotation tool with sharing-annotation capabilities.

**_This is a product of research in the field of personalized learning_**

## Use-Case diagram:
![alt text](https://github.com/Marios-R/Efront_AnnotatorPlugin/blob/master/images/Use_Case_diagram.png?raw=true "Annotator Use-Case diagram")

## Installation
Compress the files/folders in the src folder to a zip package named Annotator. 
The structure should look like this:
![alt text](https://github.com/Marios-R/Efront_AnnotatorPlugin/blob/master/images/Structure.png?raw=true "Annotator structure")

To install it, log in to eFront as an administrator and from your Administration dashboard, visit the plugins section.
You will be able to view a tabular list of all your plugins that are available in your eFront. To install a new plugin click on the 'Install plugin' button (1).
![alt text](https://github.com/Marios-R/Efront_AnnotatorPlugin/blob/master/images/installation/ins1.png?raw=true "eFront plugins list")

This will open up a pop-up window for you to upload Annotator.zip

## Start
In order to start using Annotator, you must define with the Javascript function annotate(selector) the elements where annotating text is allowed (using jQuery selectors in place of "selector"). 
You can add this Javascript in the Functionality > Javascript section of your active eFront theme.
For example the bellow will allow annotations on content units:
```javascript
$(function(){
annotate('.ef-block-content>.ef-content-area');
});
```


---
##### Libraries used
1. Selectize
2. Spectrum
3. tinyMCE
4. AnnotatorJS

##### Ideas for future research
1. Upvoting annotations.
2. Annotations on videos, images and possibly TinCan packages.
