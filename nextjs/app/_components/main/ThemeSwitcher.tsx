"use client";

import { useEffect, useRef, useState } from "react";
import { FiMoon, FiSun, FiMonitor } from "react-icons/fi";
import { useSelector, useDispatch } from "react-redux";
import { RootState } from "@/app/_lib/store";
import { setTheme } from "@/app/_lib/store/themeSlice";

type Theme = "light" | "dark" | "system";

const options: { value: Theme; icon: React.ReactNode; label: string }[] = [
  { value: "light", icon: <FiSun className="w-4 h-4" />, label: "حالت روشن" },
  { value: "system", icon: <FiMonitor className="w-4 h-4" />, label: "حالت سیستم" },
  { value: "dark", icon: <FiMoon className="w-4 h-4" />, label: "حالت تاریک" },
];

export const ThemeSwitcher = () => {
  const theme = useSelector((state: RootState) => state.theme.theme);
  const dispatch = useDispatch();
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);

  const activeIndex = options.findIndex((o) => o.value === theme);
  const activeOption = options[activeIndex];

  useEffect(() => {
    document.documentElement.classList.remove("light", "dark");
    document.documentElement.classList.add(theme);
  }, [theme]);

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
    <div dir="rtl">
      <div
        role="radiogroup"
        aria-label="تغییر تم"
        className="relative hidden md:inline-flex items-center gap-0.5 p-1 h-11 rounded-full bg-background border border-border"
      >
        <span
          className="absolute top-1 h-9 w-9 rounded-full bg-primary transition-transform duration-300"
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

        {open && (
          <div
            role="listbox"
            className="absolute top-13 right-0 flex flex-col gap-0.5 p-1 rounded-full bg-background border border-border shadow-lg z-50"
          >
            {options.map((option) => (
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
                className={`flex items-center justify-center w-9 h-9 rounded-full transition-colors ${
                  theme === option.value ? "bg-primary text-background" : "text-primary"
                }`}
              >
                {option.icon}
              </button>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default ThemeSwitcher;