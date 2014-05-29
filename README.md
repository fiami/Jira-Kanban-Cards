Jira-Kanban-Cards
=================

This project was created for printing Jira (version 6 tested) tickets as Kanban cards, in order to cut them and put them on a wall. Many plugins I found for Jira did not generate the pretty result I was looking for. In addition to the fact, that I am not a passionated Java developer, but love APIs, this tool is created with PHP and the use of the Jira REST API.

The print-ready Jira cards will look like this:

![Ticket print preview](https://raw.githubusercontent.com/fiami/Jira-Kanban-Cards/master/images/screenshots/ticket.png)

Besides the functional requirements, there have been certain technical requirements from me side as well:

Application should just run without setup
------------------------------------------
For the printing application, no database or configuration file is needed. The application can just be cloned as the webserver's root directory and get called via the browser. All necessary information will be entered for the current request via the frontend. These information are:

 * Jira-Path: Path to the API. Mostly: yourjira.tld/rest/api/2/
 * Username: Username for Jira (id required)
 * Password: Password for Jira (id required)
 * Epic: Do you want to add Epic information? (Only if Agile is used)
 * JQL: Jira Query language for selecting the tickets

Efficient structure of project
------------------------------
A good project structure is always a good idea in order to get an organized code, where every part got its own tasks (like an MVC-based approach). The structure of this project looks like this:


```
├── app
│   ├── Controller
│   │   └── CardsController.php
│   ├── Lib
│   │   └── Jira.php
│   └── Views
│       ├── Cards
│       │   ├── index.php
│       │   └── tickets.php
│       └── Layouts
│           └── default.php
├── images
│   ├── priorities
│   │   ├── blocker.png
│   │   ├── critical.png
│   │   ├── major.png
│   │   ├── minor.png
│   │   └── trivial.png
│   ├── screenshots
│   │   └── ticket.png
│   └── types
│       ├── bug.png
│       ├── epic.png
│       ├── idea.png
│       ├── improvement.png
│       ├── newfeature.png
│       ├── subtask.png
│       └── task.png
├── style
│   └── default.css
├── index.php
└── README.md

```

The application with its Controller, Lib and Views can be found in "app". The "images" and "style" directories are only made for public access in order to provide static files and the "index.php" contains our Dispatcher for this small "framework".

Views should receive plain & structured data
--------------------------------------------
The main view only gets an array of $tickets, where every item inside is an associative array of the basic fields, without any objects or other more-complex structures. By using this approach the view gets the data already in a structured way, but without any chance to change the raw information.


Plain HTML - markup only via CSS
--------------------------------
Styles via CSS are able to let everything look nicer (or worse sometimes) and this exactly is the task of CSS and should not be done via HTML. This project tries to let the HTML look as structured as possible (XML oriented) by focusing on the main information and not the way it will appear. For this reason, the generated HTML-body looks like this:

```html
<div class="ticket">
	<div class="priority major"></div>
	<div class="issuetype task"></div>
	<div class="epic epicgroup_0">Crocodile</div>
	<div class="number">DWA-37</div>
	<div class="summary">Get clock from crocodile</div>
	<div class="rank">33894</div>
	<div class="reporter">Peter Pan</div>
	<div class="assignee">Captain Hook</div>
	<div class="remaining_time">0.5 h</div>
</div>

```

Styles should work fine for printing
------------------------------------
In order to look nice for the printers, two important things were necessary to take care of:

 * The tickets need to scale very well, so that all printer settings will work
 * Only "whole" tickets should be put on a page, so that a ticket does not go over two pages (Css: "page-break-inside : avoid;")

Test API
--------
For a quick test, this application also works with public APIs, of course. These can be found here: https://quickstart.atlassian.com/en/qac/ondemand/jira/get-started/bug-tracking/public-examples/

An working example is:

 * Jira-Path: https://issues.sonatype.org/rest/api/2/
 * Username: (leave empty)
 * Password: (leave empty)
 * Epic: Exclude information
 * JQL: status = "In Progress"
