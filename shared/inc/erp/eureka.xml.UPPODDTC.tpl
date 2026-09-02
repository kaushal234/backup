<poupdat>
     <login>
          <username>TLD_US</username>
          <password>abracadabra</password>
     </login>
     <header>
          <orno>{$orno}</orno>
     </header>
     <lines>                
		{foreach item=line from=$lines}
			<line>
		       <pono>{$line.pono}</pono><ddtc>{$line.ddtc}</ddtc>
            </line>
		{/foreach}
     </lines>
</poupdat>
        