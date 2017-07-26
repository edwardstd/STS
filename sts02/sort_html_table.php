<?php
  // this routine generates some javascript that can be used to sort an html table
  // source: w3schools.com
  //
  // include or require this php file
  //
  // call the javascript routine:
  // sortTable(t,n,h) where tid = table id, n = column number to us as the sort key and h = number of header rows
  //
  print '<script>
         function sortTable(tid, n, h)
         {
           var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
           table = document.getElementById(tid);
           switching = true;

           // Set the sorting direction to ascending:
           dir = "asc"; 

           // display a message saying that the table is being sorted
           my_window = window.open("","mywindow","title=0,status=1,menu=0,height=50,width=150,left=250,top=250");
           my_window.document.write("Sorting...");

           // Make a loop that will continue until no switching has been done
           while (switching)
           {
             // start by saying: no switching is done:
             switching = false;
             rows = table.getElementsByTagName("TR");

             // Loop through all table rows (except the table headers)
             for (i = h; i < (rows.length - 1); i++)
             {
               // start by saying there should be no switching:
               shouldSwitch = false;

               // Get the two elements you want to compare, one from current row and one from the next
               x = rows[i].getElementsByTagName("TD")[n];
               y = rows[i + 1].getElementsByTagName("TD")[n];

               // check if the two rows should switch place, based on the direction, asc or desc
               if (dir == "asc")
               {
                 if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
                 {
                   // if so, mark as a switch and break the loop
                   shouldSwitch= true;
                   break;
                 }
               }
               else if (dir == "desc")
               {
                 if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
                 {
                   // if so, mark as a switch and break the loop
                   shouldSwitch= true;
                   break;
                 }
               }
             }

             if (shouldSwitch)
             {
               // If a switch has been marked, make the switch and mark that a switch has been done
               rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
               switching = true;

               // Each time a switch is done, increase this count by 1
               switchcount ++; 

             }
             else
             {
               // If no switching has been done AND the direction is "asc", set the direction
               // to "desc" and run the while loop again.
               if (switchcount == 0 && dir == "asc")
               {
                 dir = "desc";
                 switching = true;
               }
             }
           }
           // close the sorting message window
           my_window.close();
         }
       </script>';
?>
