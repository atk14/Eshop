Alerts and flash messages
=========================

## Alerts
For more information see Bootstrap documentation.

[example]
<div class="alert alert-primary" role="alert">
	A simple primary alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
<div class="alert alert-secondary" role="alert">
	A simple secondary alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
<div class="alert alert-success" role="alert">
	A simple success alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
<div class="alert alert-danger" role="alert">
	A simple danger alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
<div class="alert alert-warning" role="alert">
	A simple warning alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
<div class="alert alert-info" role="alert">
	A simple info alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
<div class="alert alert-light" role="alert">
	A simple light alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
<div class="alert alert-dark" role="alert">
	A simple dark alert with <a href="#" class="alert-link">an example link</a>. Give it a click if you like.
</div>
[/example]

### Dismissible Alert

[example]
<div class="alert alert-warning alert-dismissible fade show" role="alert">
	<strong>Holy guacamole!</strong> You should check in on some of those fields below.
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
[/example]

## Flash Message

Flash Message is alert which floats atop of page and disappears within seconds. Don't be afraid to reload this page to see it!

[example]
<div class="flash_messages">
	<div class="alert  show alert-success"><button type="button" class="close" data-dismiss="alert">×</button>Flash Message Content</em>
	</div>
	<div class="alert  show alert-warning"><button type="button" class="close" data-dismiss="alert">×</button>Flash Message Content</em>
	</div>
</div>
[/example]

### Display Flash Message by Javascript

Flash message may be displayed by Javascript:

<code>window.UTILS.FlashMessage.create( { message: "hello", style: "danger", dismissible: true } );</code>

#### Options:
- message: text to display
- style: color style - alert | danger | success | info | warning
- dismissible: if true close button would be added 

[example]
	<button class="btn btn-primary" onclick="window.UTILS.FlashMessage.create({message:'Hello there!', style: 'warning'});">Show flash message</button>
[/example]