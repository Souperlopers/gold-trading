export default function NavigationMenu() {
	const navigationList = [
		{title: "خانه", href:""},
		{title: "گزارشات", href:""},
		{title: "شارژ", href:""},
		{title: "حواله", href:""},
		{title: "تنظیمات", href:""},
	]	

	const isLastItem = (index:number) => index+1 === navigationList.length;
	

	return(
		<nav className="fixed px-5 md:right-48 md:top-5.5 bottom-0 right-0 md:w-2/3 w-full h-12 md:border-none border-t border-x rounded-t-xl bg-surface border-border">
			<ul className="flex md:justify-start justify-between items-center md:gap-10 gap-0 h-full">
				{navigationList.map((item,index)=>(
					<li key={item.title} className={`${isLastItem(index) ? "": "md:border-none border-l-2 border-border md:pl-0 sm:pl-10 pl-5"} list-none cursor-pointer text-text-primary hover:text-accent-dark`}>{item.title}</li>
				))}
			</ul>
		</nav>
	)
}
