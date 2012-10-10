 PrintableCalendarGenerator
===============================


 Introduction
-------------------------------

Because I couldn't find a large enough calendar in the shops I created one 
myself in PhotoShop/Gimp and had it printed out by a print shop. Although the
design fitted my needs, it was a lot of hassle to draw all the dates and 
occasions in by hand. Being a developer I decided it made sense to write some 
code to do the drawing for me, thus the PrintableCalendarGenerator was born.


 Usage
-------------------------------

Currently the generator consists of a rather specific index file (as in, access 
from your web-server) that generates all of the months on the calendar from
09/2012 to 08/2013, decorated with information from all the XML files found in
the 'decorations' folder. These decorations colour the backgrounds of days that 
are (school) holidays and write the holiday 'title' at the bottom of the day(s).
For birthdays, the name of the person is written at the top of the day.


 Hiatus
-------------------------------

Obviously, due to the specific implementation of the generator (and lack of 
proper documentation) it is currently not at a broadly-usable stage. As I 
continue to use the generator more, it will become more and more 
generically-implemented. It is my intention to be able to offer the functionality
of the generator to others, as people seem to like my big-ass-calendar in it's
printed out form.


 Types of holiday (observance)
-------------------------------

There are 4 different types of holidays that each have a different decoration on
the calendar. There can also be custom decorations with a icon of their own for
things such as daylight-savings, garbage day, etc.

There a 3 different shades of gray, one for weekends, one for national holidays
and one for school holidays.

* National holidays (including Religious Holidays) have their name written in
  black and have the day's background coloured gray to mark it as a day off.

 Examples of National holidays are

   - Queen's Day
   - New Year's Eve
   - Christmas
   - Easter
   - Pentecost

* Secular holidays have their name written in black but do not change the day's
  background color as we do not get a day off.

 Examples of Secular holidays are

   - Valentine's Day
   - Labour Day, Worker's Day or May Day
   - Mother's Day
   - Father's Day
   - Halloween

* School holidays
 Have their name written in  gray (or black with a white border for longer
 periods) and have the day coloured gray to mark it as a day off for the children.

 The holiday name is written in regular fashion, only stretched to the full
 length of the holiday. If a holiday spans several weeks it is only written once,
 in the first *full* week.

* Personal Observance (Birthdays) have the Persons name written in black with a
  crown over it.


* Unofficial holidays (Unofficial observances/Commemorative Days) are not yet supported

 Examples of Unofficial observances are

   - Towel Day
   -  International Talk Like a Pirate Day


 Example output
-------------------------------

![calendar][calendar_img]

[calendar_img]: https://github.com/potherca/PrintableCalendarGenerator/raw/master/calendar-2012-01.png  "Example output for the month January 2012"

--EOF--
