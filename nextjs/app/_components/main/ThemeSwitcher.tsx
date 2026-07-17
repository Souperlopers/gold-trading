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
		document.documentElement.classList.remove("light", "dark")

		const osIsDark = window.matchMedia("(prefers-color-scheme: dark)").matches
		const themeClass =
			theme === "system" ? (osIsDark ? "dark" : "light") : theme
		document.documentElement.classList.add(themeClass)
	}, [theme])

	// listen to system theme changes and update the theme accordingly
	useEffect(() => {
		const mediaQuery = window.matchMedia("(prefers-color-scheme: dark)")

		function handleThemeChange(e: MediaQueryListEvent) {
			if (theme === "system") {
        document.documentElement.classList.remove("light", "dark")
				document.documentElement.classList.toggle(e.matches?"dark":"light")
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
        className="relative hidden md:inline-flex items-center gap-0.5 p-1 h-11 rounded-full bg-background border border-border"
      >
        {/* gold background that is transformed in desktop mode */}
        <span
          className="absolute h-9 w-9 rounded-full bg-primary transition-transform duration-300"
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
            className={`relative z-10 flex items-center justify-center w-9 h-9 rounded-full transition-colors ${
              theme === option.value ? "text-background" : "text-primary"
            }`}
          >
            {option.icon}
          </button>
        ))}
      </div>

      {/* this is dispayed on mobile */}
      <div ref={containerRef} className="relative inline-block md:hidden">
        <button
          type="button"
          aria-haspopup="listbox"
          aria-expanded={open}
          aria-label="تغییر تم"
          onClick={() => setOpen((prev) => !prev)}
          className="flex items-center justify-center w-9 h-9 rounded-full bg-background border border-border text-primary transition-colors"
        >
          {activeOption.icon}
        </button>

        <div
          role="listbox"
          style={{ transform: `translateX(${(46-36)/2}px)` }}
          className={`${open ? "bg-background border p-1 shadow-lg" : "pointer-events-none border-0 px-1"} border-border transition-transform duration-300 absolute top-13 flex flex-col rounded-full z-50`}
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
              className={`${!open && "opacity-0"} flex items-center justify-center w-9 h-9 rounded-full transition-all ${theme === option.value ? "bg-primary text-background" : "text-primary"}`}
            >
              {option.icon}
            </button>
          ))}
        </div>
      </div>
    </div>
  );
};
