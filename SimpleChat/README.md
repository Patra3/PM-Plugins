#SimpleChat  
Easy-to-use and smart chat filter system. Block the words you don't want, easily.  

---  
Welcome to SimpleChat, an easy to use chat filter system that's smart.  

---

Commands:  
- /simplechat help : Lists all commands.  
- /simplechat add <word> : Adds a word into the filter list.  
- /simplechat remove <word> : Removes a word from the filter list.
- /simplechat set <1:2> : Sets the filter type.  
- /simplechat exclude <player> : Excludes a player from filter.  
- /simplechat unexclude <player> : Unexcludes a player from filter.  
- /simplechat exclusion off : Removes / stops the exclusion list.  
- /simplechat mode <replace:warn> : Replaces bad words (****) or warns user.  
Node: simplechat.main  

---

Technologies Utilitzed / Practiced:
- JSON encoding/decoding  
- File I/O  
- PocketMine API  

---

/simplechat set 1 ?  
Yes, when you set the filter type to 1, it will filter raw words, so only the fuck word or only the shit word, and a loophole can be made to bypass this. This is the weakest form of filtering, and I recommend switching to type 2 for better filtering. This mode is optimal for people who don't want to get cut off by going glass or grass.  

/simplechat set 2 ?  
Yes, when you set the filter type to 2, it will filter in a smarter way. The words spoken by player will also be analyised for similarity, for example, f u ck or fuk will still be filtered by the standard "fuck" work. But, this type also will filter "parts" of the word, etc. grass or glass. 

