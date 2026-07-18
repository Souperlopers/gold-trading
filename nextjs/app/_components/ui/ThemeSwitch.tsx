"use client";

import { useEffect, useRef, useState } from "react";
import { FiMoon, FiSun, FiMonitor } from "react-icons/fi";
import { useAppSelector, useAppDispatch } from "@/app/_lib/store";
import { setTheme } from "@/app/_lib/store/themeSlice";

const options= [
  { value: "light" , icon: <FiSun     className="w-4 h-4" />, label: "حالت روشن"  },
  { value: "system", icon: <FiMonitor className="w-4 h-4" />, label: "حالت سیستم" },
  { value: "dark"  , icon: <FiMoon    className="w-4 h-4" />, label: "حالت تاریک" },
];

export default function ThemeSwitcher() {
  const theme = useAppSelector((s) => s.theme.theme)
  const dispatch = useAppDispatch();
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  // determine which option(theme) is active
  const activeIndex = options.findIndex((o) => o.value === theme);
  const activeOption = options[activeIndex];

	// theme swapping logic
	useEffect(() => {
		// remove previous theme
		document.documentElement.removeAttribute("data-theme")

		const osIsDark = window.matchMedia("(prefers-color-scheme: dark)").matches
		const themeClass =
			theme === "system" ? (osIsDark ? "dark" : "light") : theme
		document.documentElement.setAttribute("data-theme", themeClass)
	}, [theme])

	// listen to system theme changes and update the theme accordingly
	useEffect(() => {
		const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)")

		function handleThemeChange(e: MediaQueryListEvent) {
			if (theme === "system") {
        document.documentElement.removeAttribute("data-theme")
        document.documentElement.setAttribute("data-theme", e.matches?"dark":"light")
			}
		}

		mediaQuery.addEventListener("change", handleThemeChange)
		return () => {
			mediaQuery.removeEventListener("change", handleThemeChange)
		}
	}, [])

  // close drpdown menu logic
  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    <div>
      {/* this is dispayed on desktop */}
      <div 
        role="radiogroup"
        aria-label="تغییر تم"
        className="relative hidden md:inline-flex items-center gap-0.5 p-1 h-11 rounded-full bg-base-100 border border-base-300"
      >
        {/* gold background that is transformed in desktop mode */}
        <span
          className="absolute h-9 w-9 rounded-full bg-accent transition-transform duration-300"
          style={{ transform: `translateX(${activeIndex * -38}px)` }}
        />

        {options.map((option) => (
          <button
            key={option.value}
            type="button"
            role="radio"
            aria-checked={theme === option.value}
            aria-label={option.label}
            onClick={() => dispatch(setTheme(option.value))}
            className={`relative z-10 flex items-center justify-center w-9 h-9 rounded-full transition-colors hover:bg-accent hover:cursor-pointer ${
              theme === option.value ? "text-base-content" : "text-primary"
            }`}
          >
            {option.icon}
          </button>
        ))}
      </div>

      {/* this is dispayed on mobile */}
      <div ref={containerRef} className="relative inline-block md:hidden">
        <div className="relative items-center p-1 w-11 h-11 flex justify-center align-middle rounded-full bg-base-100 border border-base-300">
					<button
						type="button"
						aria-haspopup="listbox"
						aria-expanded={open}
						aria-label="تغییر تم"
						onClick={() => setOpen((prev) => !prev)}
						className="flex items-center justify-center w-9 h-9 rounded-full bg-accent border border-base-300 text-base-content transition-colors hover:cursor-pointer"
					>
						{activeOption.icon}
					</button>
				</div>

        <div
          role="listbox"
          className={`${open ? "bg-base-100 border p-1 shadow-lg" : "pointer-events-none border-0 px-1"} border-base-300 gap-0.5 transition-transform duration-300 absolute top-13 flex flex-col rounded-full z-50`}
        >
          {options.map((option, index) => (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={theme === option.value}
              aria-label={option.label}
              onClick={() => {
                dispatch(setTheme(option.value));
                setOpen(false);
              }}
              style={{transform: !open ? `translateY(${-(index+1)*20}px)` : ""}}
              className={`${!open && "opacity-0"} flex items-center justify-center w-9 h-9 rounded-full transition-all ${theme === option.value ? "bg-accent text-base-content" : "text-primary"} hover:bg-accent hover:cursor-pointer`}
            >
              {option.icon}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
};
