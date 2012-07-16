# PrintableCalendarGenerator

## Introduction

Because I couldn't find a large enough calendar in the shops I created one 
myself in Photoshop/Gimp and had it printed out by a printshop. Although the 
design fitted my needs, it was a lot of hassle to draw all the dates and 
occasions in by hand. Being a developer I decided it made sense to write some 
code to do the drawing for me, thus the PrintableCalendarGenerator was born.

## Usage

Currently the generator consists of a rather specific index file (as in, access 
from your web-server) that generates all of the months on the calendar from
09/2011 to 08/2012, decorated with information from all the XML files found in 
the 'decorations' folder. These decorations colour the backgrounds of days that 
are (school) holidays and write the holiday 'title' at the botom of the day(s). 
For birthdays, the name of the person is written at the top of the day.

## Hiatus

Obviously, due to the specific implemenation of the generator (and lack of 
proper documentation) it is currently not at a broadly-usable stage. As I 
continue to use the generator more, it will become more and more 
genericly-implemented. It is my intention to be able to offer the functionality 
of the generator to others, as people seem to like my big-ass-callendar in it's 
printed out form.

## Example output
![calendar][calendar_img]

[calendar_img]: https://github.com/potherca/PrintableCalendarGenerator/raw/master/calendar-2012-01.png  "Example output for the month January 2012"


