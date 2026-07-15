"use client";

import { Provider } from "react-redux";
import { store } from "@/app/_store/store";

export default function ThemeProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  
  return <Provider store={store}>{children}</Provider>;
}