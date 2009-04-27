		 	function CheckAll()
			{
				count = document.mycats.elements.length;
    			for (i=0; i < count; i++)
				{
    				if(document.mycats.elements[i].checked == 1)
    				{document.mycats.elements[i].checked = 0; }
    				else {document.mycats.elements[i].checked = 1;}
				}
			}
			function UncheckAll(){

				count = document.mycats.elements.length;
   				for (i=0; i < count; i++)
				{

   					if(document.mycats.elements[i].checked == 1)
    				{document.mycats.elements[i].checked = 0; }
    				else {document.mycats.elements[i].checked = 1;}
				}
			}