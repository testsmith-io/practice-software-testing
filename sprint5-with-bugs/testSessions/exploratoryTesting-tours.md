#
#
#
#

from https://trailheadtechnology.com/tour-testing-structured-approach-to-exploratory-testing/

## Garbage Collector Tour
This is like a methodical spot check. The Garbage Collector’s tour is performed by choosing a goal 
(for example, all menu items, all error messages, all dialog boxes), and then visiting each one in the 
list by the shortest path possible. A good example of using this tour is a smoke test.
Issues revealed depend on the testing context. If it’s related to the text in the GUI, the issues will 
be mostly linguistic, and if it’s related to functionality, the issues will be functional.

## Supermodel Tour
During the Supermodel tour, the focus is not on functionality or real interaction. It’s only on the interface. 
Take the tour and watch the interface elements. Do they look good? Do they render properly, and is the 
performance good? As you make changes, does the GUI refresh properly? And so on.
Issues revealed are GUI- and UX-related.

from: https://www.techtarget.com/searchsoftwarequality/tip/Six-tours-for-exploratory-testing-the-business-district-of-your-application

## The money tour
Any guidebook will highlight the main attractions for tourists. This is where the money is made. In software 
terms, the money tour covers those features and functions that are highlighted in marketing literature. These 
are the primary functions that seen on commercials or demos when the product is getting ready for release.

## The landmark tour
Landmarks are used to get you from place to place as you travel. You look for certain spots to visit and 
then, from there, go to your next destination, often using a map to orient yourself. In software as well, 
we navigate through an application. We go from function to function, performing different types of activities.

In exploratory testing using the landmark tour, the tester would determine various key features that are 
to be visited. The navigation to these features may vary. Perhaps you can get to them using keystrokes or 
through menus. Often by changing the order in which you navigate from place to place, you can change 
conditions and uncover some underlying defects with the application.

## The intellectual tour
Imagine the intellectual that is on a city tour and asks questions of the guide. The Intellectual tour, 
similar to the Skeptical Customer tour, involves getting into the mind of someone that would be asking a 
lot of tough questions. In this tour, the tester needs to be asking the hardest questions. What is the most 
complicated function that can be executed? Test the limits. What inputs would cause the most processing? 
Which queries would be the most complex?

The questions, of course, will depend on the application under test, but the idea is to test for the most 
complex situations. Use the maximums for fields, files, sizes or anything that has a limit. 
Execute the functions of the power user.

## The FedEx tour
FedEx is responsible for picking up a package and delivering it somewhere, tracking its progress along the way. 
Similarly, the FedEx Tour is the tracking of data from a start point to an end point. 
Application data is often moved around a system, passed through various interfaces or databases. 
With the FedEx Tour, the tester needs to be able to track the data through the various stages as it flows 
through the application. Where does the data get displayed? Does the data get manipulated as it moves 
through the system? Is it delivered properly?












